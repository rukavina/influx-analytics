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

	CONST GRANULARITY_MINUTE = 'minute';
	CONST GRANULARITY_HOURLY = 'hourly';
	CONST GRANULARITY_DAILY = 'daily';
	CONST GRANULARITY_MONTHLY = 'monthly';
	
	public function __construct($db, $inputData) {
		$this->db = $db;
		$this->serviceId = isset($inputData["serviceId"]) ? $inputData["serviceId"] : null;
		$this->metrix = isset($inputData["metrix"]) ? $inputData["metrix"] : null;
		$this->startDt = isset($inputData["startDt"]) ? $inputData["startDt"] : null;
		$this->endDt = isset($inputData["endDt"]) ? $inputData["endDt"] : null;
		$this->granularity = isset($inputData["granularity"]) ? $inputData["granularity"] : null;
	}

	/**
	 * Get data
	 * @return array data
	 */
	public function getData() {
		$data = array();
 		return $data;
	}

	/**
	 * Get toal
	 * @return int total
	 */
	public function getTotal() {
		$total = 10;
		return $total;
	}

}