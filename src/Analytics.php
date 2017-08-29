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

namespace Vorbind\InfluxAnalytics;

use Vorbind\InfluxAnalytics\Mapper\AnalyticsMapperInterface;
use Vorbind\InfluxAnalytics\Exception\AnalyticsException;

/**
 *  InfluxAnalytics
 *
 *  Use this section to define what this class is doing, the PHPDocumentator will use this
 *  to automatically generate an API documentation using this information.
 *
 *  @author sasa.rajkovic
 */
class Analytics implements AnalyticsInterface {

    /*
     * @var AnalyticsMapperInterface 
     */
    protected $mapper;
    
    
    public function __construct(AnalyticsMapperInterface $mapper) {
        $this->mapper = $mapper;
    }
    
    /**
     * Get analytics data in right time zone by period and granularity 
     *  
     * @param string $rp
     * @param string $metric
     * @param array $tags
     * @param string $granularity
     * @param string $startDt
     * @param string $endDt
     * @param string $timezone
     * @return int
     * @throws AnalyticsException
     */
    public function getData($rp, $metric, $tags, $granularity = 'daily', $startDt = null, $endDt = '2100-12-01T00:00:00Z', $timezone = 'UTC') {
        $points = [];
        try {            
            $pointsRp = $this->mapper->getRpPoints($rp, $metric, $tags, $granularity, $startDt, $endDt, $timezone);
            $pointsTmp = $this->mapper->getPoints($metric, $tags, $granularity, $endDt, $timezone);

            if (count($pointsRp) > 0 || count($pointsTmp) > 0) {
                $points = $this->combineSumPoints(
                                $pointsRp, $this->fixTimeForGranularity($pointsTmp, $granularity)
                );
            }
            return $points;
        } catch (Exception $e) {
            throw new AnalyticsException("Analytics client period get data exception", 0, $e);
        }        
    }
    
    /**
     * Returns analytics total for right metric
     * 
     * @param string $rp
     * @param string $metric
     * @param array $tags
     * @return int
     * @throws AnalyticsException
     */
    public function getTotal($rp, $metric, $tags) {
        try {
            $todayDt = date("Y-m-d") . "T00:00:00Z";
            
            return  $this->mapper->getRpSum('forever', $metric, $tags) +
                    $this->mapper->getRpSum($rp, $metric, $tags, $todayDt) +
                    $this->mapper->getSum($metric, $tags);
        } catch (Exception $e) {
            throw new AnalyticsException("Analytics client get total exception", 0, $e);
        }
    }
      
    /**
     * Save analytics
     * 
     * @param string $metric
     * @param array $tags
     * @param int $value
     * @param string $date
     * @param string $rp
     * @throws AnalyticsException
     */
    public function save($metric, $tags = array(), $value = 1, $date = null, $rp = null) {
        try {
            return $this->mapper->save($metric, $tags, $value, $date, $rp);
        } catch (Exception $e) {
            throw new AnalyticsException("Analytics client save exception", 0, $e);
        }    
    }  
    
    //------------- private methods -----------//
    
    /**
     * Fix time part for non-downsampled data
     * 
     * @param array $points
     * @param string $granularity
     * @return array
     */
    private function fixTimeForGranularity($points, $granularity) {
        if ($granularity != $this->mapper::GRANULARITY_DAILY) {
            return $points;
        }
        foreach ($points as &$value) {
            $dt = strtotime($value['time']);
            $offset = substr($value['time'], -6); // get timezone offset (+02:00, -04:00)
            $value['time'] = date("Y-m-d", $dt) . "T00:00:00" . $offset;
        }
        return $points;
    }

    /**
     * Combine downsampled and non-downsampled points
     * 
     * @param array $points1
     * @param array $points2
     * @return array
     */
    private function combineSumPoints($points1, $points2) {
        $pointsCount = count($points1);
        $currPoint = 0;
        foreach ($points2 as $point2) {
            $pointFound = false;
            //leverage the fact that points are sorted and improve O(n^2)
            while ($currPoint < $pointsCount) {
                $point1 = $points1[$currPoint];
                if ($point1['time'] == $point2['time']) {
                    $points1[$currPoint]['sum'] += $point2['sum'];
                    $currPoint++;
                    $pointFound = true;
                    break;
                }
                $currPoint++;
            }
            //point not found in downsampled array, then just append
            if (!$pointFound) {
                $points1[] = $point2;
            }
        }

        return $points1;
    }    
}
