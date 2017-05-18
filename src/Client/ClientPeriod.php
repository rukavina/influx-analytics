<?php 

namespace Vorbind\InfluxAnalytics\Client;

use \Exception;
use Vorbind\InfluxAnalytics\Exception\AnalyticsException;
use Vorbind\InfluxAnalytics\Exception\AnalyticsNormalizeException;

/**
 * Client Period
 */
class ClientPeriod implements ClientInterface {

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
		$this->startDt = isset($inputData["startDt"]) ? $this->normalizeUTC($inputData["startDt"]) : null;
		$this->endDt = isset($inputData["endDt"]) ? $this->normalizeUTC($inputData["endDt"]) : null;
		$this->tags = isset($inputData["tags"]) ? $inputData["tags"] : array();
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

			$query = $this->db->getQueryBuilder()
						->count('value')
						->from($this->metrix);

			if(isset($this->startDt) && isset($this->endDt)) {
				$where[] = "time >= '". $this->startDt . "' AND time <= '" . $this->endDt . "'";
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
			
			$data = $query->getResultSet()
		          ->getPoints();
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

		if ( null == $this->tags["service"] || null == $this->metrix ) {
			throw new AnalyticsException("Client period missing some of input params.");
		}

		try {
			
			if(isset($this->startDt) && isset($this->endDt)) {
				$where[] = "time >= '". $this->startDt . "' AND time <= '" . $this->endDt . "'";
			}

			foreach($this->tags as $key => $val) {
				$where[] = "$key = '" . $val . "'";
			}

			$results = $this->db->getQueryBuilder()
					->from($this->metrix)
					->where($where)
					->sum('value')
					->getResultSet();

			$points = $results->getPoints();
		} catch (Exception $e) {
			throw new AnalyticsException("Analytics client period get total exception", 0, $e);
		}
		return isset($points[0]) && isset($points[0]["sum"]) ? $points[0]["sum"] : 0;
	}

	/**
	 * Normalize UTC 
	 * @param  string $date 
	 * @return string
	 */
	protected function normalizeUTC($date) {
		$parts = explode(" ", $date);
        if(!is_array($parts) || count($parts) != 2) {
            throw new AnalyticsNormalizeException("Error normalize date, wrong format[$date]");
        }
        return $parts[0] . "T" . $parts[1] . "Z";
	}
}