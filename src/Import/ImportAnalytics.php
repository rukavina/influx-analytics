<?php

/*
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
 * "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
 * LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR
 * A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT
 * OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL,
 * SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT
 * LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE,
 * DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY
 * THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
 * (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE
 * OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 *
 * This software consists of voluntary contributions made by many individuals
 * and is licensed under the MIT license.
 */

namespace Vorbind\InfluxAnalytics\Import;

use Vorbind\InfluxAnalytics\Mapper\ImportMapperInterface;
use Vorbind\InfluxAnalytics\AnalyticsInterface;
use Vorbind\InfluxAnalytics\Import\ImportConfigReaderInterface;
use Vorbind\InfluxAnalytics\Import\ImportAnalyticsInterface;

/**
 * Provides an API for analytics import
 */
class ImportAnalytics implements ImportAnalyticsInterface {

    /**
     *
     * @var  ImportMapperInterface
     */
    protected $mapper;

    /**
     *
     * @var AnalyticsInterface
     */
    protected $analytics;

    /**
     *
     * @var ImportConfigReaderInterface 
     */
    protected $reader;

    public function __construct(ImportMapperInterface $mapper, AnalyticsInterface $analytics, ImportConfigReaderInterface $reader) {
        $this->mapper = $mapper;
        $this->analytics = $analytics;
        $this->reader = $reader;
    }

    /**
     * Execute import
     */
    public function execute($now) {
        try {
            $metrics = $this->reader->getMetricsConfig();
            foreach ($metrics as $metric => $config) {                
                if (!$this->isMetricValid($config)) {
                    print("Metric configuration is not valid!");
                    throw new Exception("Metric configuration is not valid!");
                }
                
                $rps = array_keys($config["mysql"]["query"]);
                foreach($rps as $rp) {
                    print("Import metric[$metric] for rp[$rp]\n");
                    $this->importMetric($now, $metric, $config, $rp);
                }
            }
        } catch (Exception $e) {
            printf("Error importing data:" . $e->getMessage(), PHP_EOL);
        }
    }
    
    /**
     * Import metric
     * 
     * @param string $now
     * @param string $metric
     * @param array $config
     * @param string $rp
     */
    protected function importMetric($now, $metric, $config, $rp) {
        $offset = 0;
        $limit = 100;
        while (true) {          
            $query = sprintf($config["mysql"]["query"][$rp], $limit, $offset);
            $rows = $this->mapper->getRows($query);

            if (count($rows) <= 0) {
                break;
            }

            foreach ($rows as $row) {
                $tags = array_intersect_key($row, $config["influx"]["tags"]);
                if(!$this->areTagsValid($tags) || !$this->isUtcValid($tags["utc"], $now, $rp)) {
                    continue;
                }
                
                $value = $tags["value"];
                $utc = $tags["utc"];
                
                unset($tags["value"]);
                unset($tags["utc"]);
                
                $this->analytics->save($metric, $tags, intval($value), $utc, $rp);                
            }
            $offset += $limit;
            usleep(300000);
        }
    }
    
    /**
     * Is valid utc
     * 
     * @param string $utc
     * @param string $now
     * @param string $rp
     * @return boolean
     */
    protected function isUtcValid($utc, $now, $rp) {
        if( 'forever' == $rp) {
            $nowDate = date('Y:m:d', strtotime($now));
            if(strtotime($utc) >= strtotime($nowDate)) {
                return false;
            }
        } 
        if( 'years_5' == $rp) {
            $min = date('i', strtotime($now));
            $minutes = $min > 45 ? 45 : ( $min > 30 ? 30 : ( $min > 15 ? 15 : '00') );       
            $nowLimit = date('Y:m:d', strtotime($now)) . "T" . date('H', strtotime($now)) . ":$minutes:00Z";
            if(strtotime($utc) >= strtotime($nowLimit)) {
                return false;
            }
        }
        return true;
    }
    
    /**
     * Check if metric is valid
     * 
     * @param array $metric
     * @return boolean
     */
    protected function isMetricValid($metric) {
        if (!isset($metric) || !is_array($metric) 
                || !isset($metric["influx"]) || !isset($metric["influx"]["tags"]) 
                || !isset($metric["mysql"]) || !isset($metric["mysql"]["query"])) {
                    return false;
        }
        return true;
    }
    
    /**
     * Check if tags are valid
     * 
     * @param array $tags
     * @return boolean
     */
    protected function areTagsValid($tags) {
        foreach ($tags as $val) {
            if (!isset($val)) {
                return false;
            }
        }
        return true;
    }
}
