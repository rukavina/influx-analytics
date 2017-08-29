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

namespace Vorbind\InfluxAnalytics\Mapper;

use InfluxDB\Database as InfluxDB;
use InfluxDB\Point;
use Vorbind\InfluxAnalytics\Mapper\AnalyticsMapperInterface;

/**
 * Analytics mapper
 */
class AnalyticsMapper implements AnalyticsMapperInterface {
    
    use \Vorbind\InfluxAnalytics\AnalyticsTrait;
        
    CONST GRANULARITY_HOURLY = 'hourly';
    CONST GRANULARITY_DAILY = 'daily';
    CONST GRANULARITY_WEEKLY = 'weekly';
    
    /**
     * @var InfluxDB
     */
    protected $db; 
    
    public function __construct(InfluxDB $db) {
        $this->db = $db;
    }
    
    /**
     * Get points from retention policy
     * 
     * @param string $rp
     * @param string $metric
     * @param array $tags
     * @param string $granularity
     * @param string $startDt
     * @param string $endDt
     * @param string $timezone
     * @return array
     */
    public function getRpPoints($rp, $metric, $tags, $granularity, $startDt, $endDt, $timezone) {
        
        if (null == $rp || null == $metric) {
            return [];
        }
        
        $where = [];
        
        $query = $this->db->getQueryBuilder()
                ->retentionPolicy($rp)
                ->sum('value')
                ->from($metric);

        if (isset($endDt)) {
            $where[] = "time <= '" . $endDt . "'";
        }
        
        if (isset($startDt)) {
            $where[] = "time >= '" . $startDt . "'";
        }
        
        foreach ($tags as $key => $val) {
            $where[] = "$key = '" . $val . "'";
        }

        $query->where($where);
        
        $groupBy = "time(1d) tz('" . $timezone . "')";
        if ($granularity == self::GRANULARITY_HOURLY) {
            //timeoffset doesn't work hourly
            $groupBy = "time(1h) tz('" . $timezone . "')";
        } else if ($granularity == self::GRANULARITY_DAILY) {
            $groupBy = "time(1d) tz('" . $timezone . "')";
        } else if ($granularity == self::GRANULARITY_WEEKLY) {
            $groupBy = "time(1w) tz('" . $timezone . "')";
        }
        $query->groupBy($groupBy);

        return $query->getResultSet()->getPoints();
    }
    
    /**
     * Get points from default retention policy
     * 
     * @param string $metric
     * @param array $tags
     * @param string $granularity
     * @param string $endDt
     * @param string $timezone
     * @return array
     */
    public function getPoints($metric, $tags, $granularity, $endDt, $timezone) {
        if ( null == $metric ) {
            return [];
        }
        
        $where = [];

        $now = $this->normalizeUTC(date("Y-m-d H:i:s"));
        
        $min = intval(date('i'));
        $minutes = $min > 45 ? 45 : ( $min > 30 ? 30 : ( $min > 15 ? 15 : '00') );       
        $lastHourDt = date("Y-m-d") . "T" . date('H') . ":$minutes:00Z";
        
        if (strtotime($endDt) < strtotime($lastHourDt)) {
           return [];
        }
        
        $where[] = "time >= '" . $lastHourDt . "' AND time <= '" . $now  . "'";
        
        foreach ($tags as $key => $val) {
            $where[] = "$key = '" . $val . "'";
        }
        
        $query = $this->db->getQueryBuilder()
                ->sum('value')
                ->from($metric)
                ->where($where);

        $groupBy = "time(1d) tz('" . $timezone . "')";
        if ($granularity == self::GRANULARITY_HOURLY) {
            $groupBy = "time(1h) tz('" . $timezone . "')";
        } else if ($granularity == self::GRANULARITY_DAILY) {
            $groupBy = "time(1d) tz('" . $timezone . "')";
        } else if ($granularity == self::GRANULARITY_WEEKLY) {
            $groupBy = "time(1w) tz('" . $timezone . "')";
        }
        $query->groupBy($groupBy);
        
        return $query->getResultSet()->getPoints();        
    }
    
    /**
     * Get total from retention policy
     * 
     * @param string $rp
     * @param string $metric
     * @param array $tags
     * @param string $startDt
     * @param string $endDt
     * @return int
     */
    public function getRpSum($rp, $metric, $tags, $startDt, $endDt) {
        if (null == $rp || null == $metric) {
            return 0;
        }
        
        $where = [];
        
        if (isset($startDt)) {
            $where[] = "time >= '" . $startDt . "'";
        }
        
        if (!isset($endDt)) {
            $endDt = '2100-01-01T00:00:00Z';
        }
        $where[] = "time <= '" . $endDt . "'";
    
        foreach ($tags as $key => $val) {
            $where[] = "$key = '" . $val . "'";
        }

        $points = $this->db->getQueryBuilder()
                ->retentionPolicy($rp)
                ->from($metric)
                ->where($where)
                ->sum('value')
                ->getResultSet()
                ->getPoints();
                
        return isset($points[0]) && isset($points[0]["sum"]) ? $points[0]["sum"] : 0;
    }
    
    /**
     * Get total from default retention policy
     * 
     * @param string $metric
     * @param array $tags
     * @param string $endDt
     * @return int
     */
    public function getSum($metric, $tags, $endDt) {
        if (null == $metric) {
            return 0;
        }
        
        $min = intval(date('i'));
        $minutes = $min > 45 ? 45 : ( $min > 30 ? 30 : ( $min > 15 ? 15 : '00') );       
        $lastHourDt = date("Y-m-d") . "T" . date('H') . ":$minutes:00Z";
  
        $where = [];
        
        if (!isset($endDt)) {
            $endDt = '2100-01-01T00:00:00Z';
        }
        
        if (strtotime($endDt) < strtotime($lastHourDt)) {
           return 0;
        }
        
        $where[] = "time >= '" . $lastHourDt . "' AND time <= '" . $endDt . "'";
        
        foreach ($tags as $key => $val) {
            $where[] = "$key = '" . $val . "'";
        }

        $points = $this->db->getQueryBuilder()
                ->from($metric)
                ->where($where)
                ->sum('value')
                ->getResultSet()
                ->getPoints();
        
        return isset($points[0]) && isset($points[0]["sum"]) ? $points[0]["sum"] : 0;
    } 
    
    /**
     * Save analytics
     *     
     * @param string $metric
     * @param array $tags
     * @param int $value
     * @param string $date
     * @param string $rp
     * @return void
     * @throws AnalyticsException
     */
    public function save($metric, $tags = array(), $value = 1, $date = null, $rp = null) {
        try {
            $command = isset($date) ? " -d '" . $this->normalizeUTC($date) . "'" : "";
            // Time precision is in nanaoseconds
            $timeNs = exec("date $command +%s%N"); 
            $fields = array();

            $points = array(
                new Point(
                        $metric, 
                        $value, 
                        $tags, 
                        $fields, 
                        $timeNs
                )
            );
            return $this->db->writePoints($points, InfluxDB::PRECISION_NANOSECONDS, $rp);
        } catch (Exception $e) {
            throw new AnalyticsException("Error saving analytics data", 0, $e);
        }
    }
}