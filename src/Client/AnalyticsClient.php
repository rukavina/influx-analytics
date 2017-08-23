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

namespace Vorbind\InfluxAnalytics\Client;

use InfluxDB\Database as InfluxDB;
use Vorbind\InfluxAnalytics\Client\AnalyticsEntity;
use Vorbind\InfluxAnalytics\Exception\AnalyticsException;


class AnalyticsClient implements AnalyticsClientInterface
{
    
    use \Vorbind\InfluxAnalytics\AnalyticsTrait;
    
    CONST GRANULARITY_HOURLY = 'hourly';
    CONST GRANULARITY_DAILY = 'daily';
    CONST GRANULARITY_WEEKLY = 'weekly';
    
    /**
     * @var InfluxDB
     */
    protected $db;   
    
    /**
     * @var AnalyticsEntity
     */
    protected $entity;
        
    public function __construct(InfluxDB $db, AnalyticsEntity $entity) {
        $this->db = $db;
        $this->entity = $entity;        
    }
    
    /**
     * Get analytics data in right time zone by period and granularity 
     * 
     * @param string $granularity
     * @param string $startDt
     * @param string $endDt
     * @param string $timezone
     */
    public function getData($granularity = 'daily', $startDt = null, $endDt = '2100-12-01T00:00:00Z', $timezone = 'UTC') {
        $points = [];
        
        if ( !isset($this->entity->tags["service"]) || null == $this->entity->metric ) {
            throw new AnalyticsException("Analytics client getData faild, missing input data");
        }

        try {            
            if($this->entity->rp) {
                $pointsRp = $this->getRpPoints($granularity, $startDt, $endDt, $timezone);
                $pointsCurrent = $this->getCurrentPoints($granularity, $endDt, $timezone);
                
                $points = $this->combineSumPoints(
                                $pointsRp, $this->fixTimeForGranularity($pointsCurrent, $granularity)
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
     * @return int
     */
    public function getTotal($startDt = null, $endDt = null) {

        if ( !isset($this->entity->tags["service"]) || null == $this->entity->metric ) {
            throw new AnalyticsException("Get total missing input data.");
        }

        try {
            return $this->getRpSum($startDt, $endDt) + $this->getCurrentSum($endDt);
        } catch (Exception $e) {
            throw new AnalyticsException("Analytics client get total exception", 0, $e);
        }
    }
        
    /**
     * Get points from retention policy
     * 
     * @param string $granularity
     * @param string $startDt
     * @param string $endDt
     * @param string $timezone
     * @return array
     */
    private function getRpPoints($granularity, $startDt, $endDt, $timezone) {
        $timeoffset = $this->getTimezoneHourOffset($timezone);
        $where = [];
        
        $query = $this->db->getQueryBuilder()
                ->retentionPolicy($this->entity->rp)
                ->sum('value')
                ->from($this->entity->metric);

        if (isset($endDt)) {
            $where[] = "time <= '" . $endDt . "' + $timeoffset";
        }
        
        if (isset($startDt)) {
            $where[] = "time >= '" . $startDt . "' + $timeoffset";
        }
        
        foreach ($this->entity->tags as $key => $val) {
            $where[] = "$key = '" . $val . "'";
        }

        $query->where($where);
        
        if(!isset($granularity)) {
            $query->groupBy('time(1d)');
        } else if ($granularity == self::GRANULARITY_HOURLY) {
            $query->groupBy('time(1h)');
        } else if ($granularity == self::GRANULARITY_DAILY) {
            $query->groupBy('time(1d)');
        } else if ($granularity == self::GRANULARITY_WEEKLY) {
            $query->groupBy('time(1w)');
        }

        return $query->getResultSet()->getPoints();
    }
    
    /**
     * Get points from default retention policy
     * 
     * @param string $granularity
     * @param string $endDt
     * @param string $timezone
     * @return array
     */
    private function getCurrentPoints($granularity, $endDt, $timezone) {
        $where = [];

        $timeoffset = $this->getTimezoneHourOffset($timezone);
        $now = $this->normalizeUTC(date("Y-m-d H:i:s"));
        $lastHourDt = date("Y-m-d") . "T" . date('H') . ":00:00Z";
        
        if (strtotime($endDt) < strtotime($lastHourDt)) {
           return [];
        }
        
        $where[] = "time >= '" . $lastHourDt . "' + $timeoffset AND time <= '" . $now  . "' + $timeoffset";
        
        foreach ($this->entity->tags as $key => $val) {
            $where[] = "$key = '" . $val . "'";
        }
        
        $query = $this->db->getQueryBuilder()
                ->sum('value')
                ->from($this->entity->metric)
                ->where($where);

        if(!isset($granularity)) {
            $query->groupBy('time(1d)');
        } else if ($granularity == self::GRANULARITY_HOURLY) {
            $query->groupBy('time(1h)');
        } else if ($granularity == self::GRANULARITY_DAILY) {
            $query->groupBy('time(1d)');
        } else if ($granularity == self::GRANULARITY_WEEKLY) {
            $query->groupBy('time(1w)');
        }

        return $query->getResultSet()->getPoints();        
    }
    
    /**
     * Get total from retention policy
     * 
     * @param string $startDt
     * @param string $endDt
     * @return int
     */
    private function getRpSum($startDt, $endDt) {
        $where = [];
        
        if(!$this->entity->rp) {
            return 0;
        }
        
        if (!isset($endDt)) {
            $endDt = '2100-01-01T00:00:00Z';
        }
        $where[] = "time <= '" . $endDt . "'";
        
        if (isset($startDt)) {
            $where[] = "time >= '" . $startDt . "'";
        }
        

        foreach ($this->entity->tags as $key => $val) {
            $where[] = "$key = '" . $val . "'";
        }

        $points = $this->db->getQueryBuilder()
                ->retentionPolicy($this->entity->rp)
                ->from($this->entity->metric)
                ->where($where)
                ->sum('value')
                ->getResultSet()
                ->getPoints();
                
        return isset($points[0]) && isset($points[0]["sum"]) ? $points[0]["sum"] : 0;
    }
    
    /**
     * Get total from default retention policy
     * @param string $endDt
     * @param string $timezone
     * @return int
     */
    private function getCurrentSum($endDt) {
        $lastHourDt = date("Y-m-d") . "T" . date('H') . ":00:00Z";
        $where = [];
        
        if (!isset($endDt)) {
            $endDt = '2100-01-01T00:00:00Z';
        }
        
        if (strtotime($endDt) < strtotime($lastHourDt)) {
           return 0;
        }
        
        $where[] = "time >= '" . $lastHourDt . "' AND time <= '" . $endDt . "'";
        
        foreach ($this->entity->tags as $key => $val) {
            $where[] = "$key = '" . $val . "'";
        }

        $points = $this->db->getQueryBuilder()
                ->from($this->entity->metric)
                ->where($where)
                ->sum('value')
                ->getResultSet()
                ->getPoints();
        
        return isset($points[0]) && isset($points[0]["sum"]) ? $points[0]["sum"] : 0;
    }
    
    /**
     * Fix time part for non-downsampled data
     * 
     * @param array $points
     * @param string $granularity
     * @return array
     */
    private function fixTimeForGranularity($points, $granularity) {
        if ($granularity != self::GRANULARITY_DAILY) {
            return $points;
        }
        foreach ($points as &$value) {
            $dt = strtotime($value['time']);
            $value['time'] = date("Y-m-d", $dt) . "T00:00:00Z";
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
