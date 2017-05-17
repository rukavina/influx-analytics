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
		try {
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
		} catch (Exception $e) {
			throw new AnalyticsException("Analytics client total get total exception", 0, $e);
		}
		return isset($points[0]) && isset($points[0]["sum"]) ? $points[0]["sum"] : 0;
	}

	protected function normalizeUTC($date) {
        $parts = explode(" ", $date);
        if(!is_array($parts) || count($parts) != 2) {
            throw new AnalyticsNormalizeException("Error normalize date, wrong format[$date]");
        }
        return $parts[0] . "T" . $parts[1] . "Z";
    } 
}