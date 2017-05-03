<?php 

namespace Vorbind\InfluxAnalytics\Client;

use \Exception;

/**
 * Client Period
 */
class ClientPeriod implements ClientInterface {

	protected $db;
	protected $serviceId;
	protected $metrix;
	protected $startDt;
	protected $endDt;
	protected $granularity;

	CONST GRANULARITY_HOURLY = 'hourly';
	CONST GRANULARITY_DAILY = 'daily';
	CONST GRANULARITY_WEEKLY = 'weekly';
	CONST GRANULARITY_MONTHLY = 'monthly';
	CONST GRANULARITY_YEARLY = 'yearly';
	
	public function __construct($db, $inputData) {
		$this->db = $db;
		$this->serviceId = isset($inputData["serviceId"]) ? $inputData["serviceId"] : null;
		$this->metrix = isset($inputData["metrix"]) ? $inputData["metrix"] : null;
		$this->startDt = isset($inputData["startDt"]) ? $inputData["startDt"] : null;
		$this->endDt = isset($inputData["endDt"]) ? $inputData["endDt"] : null;
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

		$query = $this->db->getQueryBuilder()
					->select('news')
					->count('value')
					->from('sms');

		$where[] = "service='" . $this->serviceId . "'";
		$where[] = "time >= '". $this->startDt . "' AND time <= '" . $this->endDt . "'";
		foreach($this->tags as $key => $val) {
			$where[] = "$key = '" . $val . "'";
		}

		$query->where($where);

		//granularity
		if( $this->granularity == self::GRANULARITY_HOURLY ) {
			$query->groupBy('time(1h)');
			//$query .= " GROUP BY time(1h)";
		}	
		else if( $this->granularity == self::GRANULARITY_WEEKLY ) {
			$query->groupBy('time(1w)');
			//$query .= " GROUP BY time(1w)";
		}
		//daily by default
		else {
			$query->groupBy('time(1d)');
			//$query .= " GROUP BY time(1d)";
		}	
		
		$data = $query->getResultSet()
	          ->getPoints();

		return $data;
	}

	/**
	 * Get toal
	 * @return int total
	 */
	public function getTotal() {
		$where = [];
		
		$where[] = "service='" . $this->serviceId . "'";
		foreach($this->tags as $key => $val) {
			$where[] = "$key = '" . $val . "'";
		}

		$results = $this->db->getQueryBuilder()
				->select('news')
				->from($this->metrix)
				->where($where)
				->sum('value')
				->getResultSet();

		$points = $results->getPoints();
		return isset($points[0]) && isset($points[0]["sum"]) ? $points[0]["sum"] : 0;
	}

}