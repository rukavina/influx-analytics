<?php 

namespace Vorbind\InfluxAnalytics\Client;

use \Exception;
use Vorbind\InfluxAnalytics\Exception\AnalyticsException;
use Vorbind\InfluxAnalytics\Exception\AnalyticsNormalizeException;


/**
 * Client Total
 */
class ClientTotal implements ClientInterface {

	protected $db;
	protected $service;
	protected $metrix;
	protected $granularity;

	public function __construct($db, $inputData) {
		$this->db = $db;
		$this->metrix = isset($inputData["metrix"]) ? $inputData["metrix"] : null;
		$this->tags = isset($inputData["tags"]) ? $inputData["tags"] : array();
	}
	
	/**
	 * Get toal
	 * @return int total
	 */
	public function getTotal() {
		$where = [];

		if ( null == $this->tags["service"] || null == $this->metrix ) {
			throw new AnalyticsException("Client total missing some of input params.");
		}

		try {
			
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
			throw new AnalyticsException("Analytics client total get total exception", 0, $e);
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