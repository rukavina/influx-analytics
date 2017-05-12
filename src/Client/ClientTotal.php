<?php 

namespace Vorbind\InfluxAnalytics\Client;

use \Exception;

/**
 * Client Total
 */
class ClientTotal implements ClientInterface {

	protected $db;
	protected $service;
	protected $metrix;
	protected $date;
	protected $granularity;

	public function __construct($db, $inputData) {
		$this->db = $db;
		$this->metrix = isset($inputData["metrix"]) ? $inputData["metrix"] : null;
		$this->tags = isset($inputData["tags"]) ? $inputData["tags"] : array();
		$this->service = isset($this->tags["service"]) ? $this->tags["service"] : null;
		$this->date = isset($inputData["date"]) ? $this->normalizeUTC($inputData["date"]) : null;
	}
	
	/**
	 * Get toal
	 * @return int total
	 */
	public function getTotal() {
		$where = [];
		
		if ( null == $this->service || null == $this->metrix ) {
			throw new Exception("Client period missing some of input params.");
		}

		if (!isset($this->tags["service"])) {
			$where[] = "service='" . $this->service . "'";
		}
		$where[] = null != $this->date ? "time <= '" . $this->date . "'" : "time >= '2016-01-11T00:00:00Z'";
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

	protected function normalizeUTC($date) {
		$parts = explode(" ", $date);
		return is_array($parts) && count($parts) == 2  ? $parts[0] . "T" . $parts[1] . "Z" : $date;
	}

}