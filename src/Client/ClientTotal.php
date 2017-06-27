<?php 

namespace Vorbind\InfluxAnalytics\Client;

use \Exception;
use Vorbind\InfluxAnalytics\Exception\AnalyticsException;
use Vorbind\InfluxAnalytics\Exception\AnalyticsNormalizeException;


/**
 * Client Total
 */
class ClientTotal implements ClientInterface {

	use \Vorbind\InfluxAnalytics\AnalyticsTrait;

	protected $db;
	protected $service;
	protected $metrix;
	protected $granularity;
	protected $retentionPolicy;

	public function __construct($db, $inputData) {
		$this->db = $db;
		$this->metrix = isset($inputData["metrix"]) ? $inputData["metrix"] : null;
		$this->rp = isset($inputData["rp"]) ? $inputData["rp"] : null;
		$this->tags = isset($inputData["tags"]) ? $inputData["tags"] : array();
	}
	
	/**
	 * Get toal
	 * @return int total
	 */
	public function getTotal() {
		$where = [];
		$sum = 0;

		if ( null == $this->tags["service"] || null == $this->metrix ) {
			throw new AnalyticsException("Client total missing some of input params.");
		}

		try {
			$lastHourDt = date("Y-m-d") . "T" . date('H') . ":00:00Z";
			// if you not set max time he takas current date as max time
			$where[] = "time <= '2099-01-01T00:00:00Z'";
			if (null != $this->rp) {
				$where[] = "time > '" . $lastHourDt . "'";
			}

			foreach($this->tags as $key => $val) {
				$where[] = "$key = '" . $val . "'";
			}
		
			$query = $this->db->getQueryBuilder()
			        ->from($this->metrix)
					->where($where)
					->sum('value')
					;

			$points = $query->getResultSet()->getPoints();
			$sum += isset($points[0]) && isset($points[0]["sum"]) ? $points[0]["sum"] : 0;

			if (null != $this->rp) { 
				$where = ["time <= '2099-01-01T00:00:00Z'"];
				$query = $this->db->getQueryBuilder()
				    ->retentionPolicy($this->rp)
			        ->from($this->metrix)
					->where($where)
					->sum('value')
					;
			    $rpPoints = $query->getResultSet()->getPoints();
			    $sum += isset($rpPoints[0]) && isset($rpPoints[0]["sum"]) ?  $rpPoints[0]["sum"] : 0;
            } 

		} catch (Exception $e) {
			throw new AnalyticsException("Analytics client total get total exception", 0, $e);
		}
		return $sum;
	}
}