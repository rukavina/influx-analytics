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
		$data = array();
		$where = array();

		if ( null == $this->tags["service"] || null == $this->metrix ) {
			throw new AnalyticsException("Client period missing some of input params.");
		}

		try {

			$timeoffset = $this->getTimezoneHourOffset($this->timezone);
			
			$query = $this->db->getQueryBuilder()
						->sum('value')
						->from($this->metrix);

			if(isset($this->startDt) && isset($this->endDt)) {
				$where[] = "time >= '". $this->startDt . "' + $timeoffset AND time <= '" . $this->endDt . "' + $timeoffset";
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
			
			$data = $query->getResultSet()->getPoints();

		    if (null != $this->rp) {      
			    $query->retentionPolicy($this->rp);      
			    $rpData = $query->getResultSet()->getPoints();
		        $data +=$rpData;          
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
			
			if(isset($this->startDt) && isset($this->endDt)) {
				$where[] = "time >= '". $this->startDt . "' + $timeoffset AND time <= '" . $this->endDt . "' + $timeoffset";
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

			if (null != $this->rp) {      
			    $query->retentionPolicy($this->rp);
			    $rpPoints = $query->getResultSet()->getPoints();
			    $sum += isset($rpPoints[0]) && isset($rpPoints[0]["sum"]) ?  $rpPoints[0]["sum"] : 0;
            } 

		} catch (Exception $e) {
			throw new AnalyticsException("Analytics client period get total exception", 0, $e);
		}
		return $sum;
	}
}