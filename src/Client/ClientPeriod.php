<?php 

namespace Vorbind\InfluxAnalytics\Client;

use \Exception;
use Vorbind\InfluxAnalytics\Exception\AnalyticsException;
use Vorbind\InfluxAnalytics\Exception\AnalyticsNormalizeException;

/**
 * Client Period
 */
class ClientPeriod implements ClientInterface {

	use \Vorbind\InfluxAnalytics\AnalyticsTrait;

	protected $db;
	protected $metrix;
	protected $startDt;
	protected $endDt;
	protected $granularity;

	CONST GRANULARITY_HOURLY = 'hourly';
	CONST GRANULARITY_DAILY = 'daily';
	CONST GRANULARITY_WEEKLY = 'weekly';
	
	public function __construct($db, $inputData) {
		$this->db = $db;
		$this->metrix = isset($inputData["metrix"]) ? $inputData["metrix"] : null;
		$this->rp = isset($inputData["rp"]) ? $inputData["rp"] : null;
		$this->startDt = isset($inputData["startDt"]) ? $this->normalizeUTC($inputData["startDt"]) : null;
		$this->endDt = isset($inputData["endDt"]) ? $this->normalizeUTC($inputData["endDt"]) : null;
		$this->tags = isset($inputData["tags"]) ? $inputData["tags"] : array();
		$this->timezone = isset($inputData["timezone"]) ? $inputData["timezone"] : 'UTC';
		$this->granularity = isset($inputData["granularity"]) ? $inputData["granularity"] : null;
	}

	/**
	 * Get data
	 * @return array data
	 */
	public function getData() {
		$data = [];
		
		if ( null == $this->tags["service"] || null == $this->metrix ) {
			throw new AnalyticsException("Client period missing some of input params.");
		}

		try {
			
			$timeoffset = $this->getTimezoneHourOffset($this->timezone);
			$lastHourDt = date("Y-m-d") . "T" . date('H') . ":00:00Z";
			
		    if ($this->rp) {  
		    	$whereRp = [];
		    	$queryRp = $this->db->getQueryBuilder()
		    			->retentionPolicy($this->rp)
						->sum('value')
						->from($this->metrix);

				if(isset($this->startDt) && isset($this->endDt)) {
					$whereRp[] = "time >= '". $this->startDt . "' + $timeoffset AND time <= '" . $this->endDt . "' + $timeoffset";
				}

				foreach($this->tags as $key => $val) {
					$whereRp[] = "$key = '" . $val . "'";
				}
			
				$queryRp->where($whereRp);

				//granularity
				if( $this->granularity == self::GRANULARITY_HOURLY ) {
					$queryRp->groupBy('time(1h)');
				}	
				else if( $this->granularity == self::GRANULARITY_WEEKLY ) {
					$queryRp->groupBy('time(1w)');
				}
				//daily by default
				else {
					$queryRp->groupBy('time(1d)');
				}	

				//$queryRp->fillWith(0);
				    
			    $data = $queryRp->getResultSet()->getPoints();  
		    }

		    // TODO: check case if 

		    if (!$this->rp || strtotime($this->endDt) > strtotime($lastHourDt)) {
		    	$now = $this->normalizeUTC(date("Y-m-d H:i:s"));
				$where = [];
		    	$startDt = $this->startDt;
				if (strtotime($this->endDt) > strtotime($lastHourDt) && null != $this->rp) {
					$startDt = $lastHourDt; //last hour date
				}

				$query = $this->db->getQueryBuilder()
						->sum('value')
						->from($this->metrix);


				if(isset($startDt) && isset($this->endDt)) {
					$where[] = "time >= '". $startDt . "' AND time <= '" . $now  . "'";
				}

				foreach($this->tags as $key => $val) {
					$where[] = "$key = '" . $val . "'";
				}
			
				$query->where($where);

				//granularity
				if( $this->granularity == self::GRANULARITY_HOURLY ) {
					$query->groupBy('time(1h)');
				}	
				else if( $this->granularity == self::GRANULARITY_WEEKLY ) {
					$query->groupBy('time(1w)');
				}
				//daily by default
				else {
					$query->groupBy('time(1d)');
				}

				//$query->fillWith(0);
				$dataTmp = $query->getResultSet()->getPoints();
                                
                                //merge (update, or append) to downsampled data
                                return $this->combineSumPoints(
                                    $data,
                                    $this->fixTimeForGranularity($dataTmp, $this->granularity)
                                );                                

				/*foreach($dataTmp as $item) {
					$key = $this->arrayMultiSearch($item['time'], $data);
					$data[$key] = $dataTmp[0];
                                }*/
			}

	  	} catch (Exception $e) {
      		throw new AnalyticsException("Analytics client period get data exception");
      	}

		return $data;
	}

	/**
	 * Get total
	 * @return int total
	 */
	public function getTotal() {
		$where = [];
		$points = [];
		$sum = 0;

		if ( null == $this->tags["service"] || null == $this->metrix ) {
			throw new AnalyticsException("Client period missing some of input params.");
		}

		try {

			$timeoffset = $this->getTimezoneHourOffset($this->timezone);			
			$lastHourDt = date("Y-m-d") . "T" . date('H') . ":00:00Z";
			
			if (null == $this->rp || strtotime($this->endDt) > strtotime($lastHourDt)) {
				$where = [];

				$startDt = $this->startDt;
				if (strtotime($this->endDt) > strtotime($lastHourDt) && null != $this->rp) {
					$startDt = $lastHourDt; //last hour date
				}
				
				if(isset($startDt) && isset($this->endDt)) {
					$where[] = "time >= '". $startDt . "' AND time <= '" . $this->endDt . "'";
				}

				foreach($this->tags as $key => $val) {
					$where[] = "$key = '" . $val . "'";
				}

				$query = $this->db->getQueryBuilder()
						->from($this->metrix)
						->where($where)
						->sum('value')
						->getResultSet();

				$points = $query->getPoints();
				$sum += isset($points[0]) && isset($points[0]["sum"]) ?  $points[0]["sum"] : 0;
			}

			if (null != $this->rp) { 
				$whereRp = [];
				foreach($this->tags as $key => $val) {
					$whereRp[] = "$key = '" . $val . "'";
				}

				if(isset($this->startDt) && isset($this->endDt)) {
					$whereRp[] = "time >= '". $this->startDt . "' AND time <= '" . $this->endDt . "'";
				}

			    $query2 = $this->db->getQueryBuilder()
			            ->retentionPolicy($this->rp)
						->from($this->metrix)
						->where($whereRp)
						->sum('value')
						->getResultSet();
			    $rpPoints = $query2->getPoints();
			    $sum += isset($rpPoints[0]) && isset($rpPoints[0]["sum"]) ?  $rpPoints[0]["sum"] : 0;
            } 

		} catch (Exception $e) {
			throw new AnalyticsException("Analytics client period get total exception", 0, $e);
		}
		return $sum;
	}
        
    /**
     * Fix time part for non-downsampled data
     * 
     * @param array $points
     * @param string $granularity
     * @return array
     */
    protected function fixTimeForGranularity($points, $granularity)
    {
        if($granularity != self::GRANULARITY_DAILY){
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
    protected function combineSumPoints($points1, $points2)
    {
        $pointsCount = count($points1);
        $currPoint = 0;
        foreach ($points2 as $point2) {
            $pointFound = false;
            //leverage the fact that points are sorted and improve O(n^2)
            while($currPoint < $pointsCount){
                $point1 = $points1[$currPoint];
                if($point1['time'] == $point2['time']){
                    $points1[$currPoint]['sum'] += $point2['sum'];
                    $currPoint++;
                    $pointFound = true;
                    break;
                }                
                $currPoint++;
            }
            //point not found in downsampled array, then just append
            if(!$pointFound){
                $points1[] = $point2;
            }            
        }
        
        return $points1;
    }
        
}