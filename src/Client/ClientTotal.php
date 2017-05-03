<?php 

namespace Vorbind\InfluxAnalytics\Client;

use \Exception;

/**
 * Client Total
 */
class ClientTotal implements ClientInterface {

	protected $db;
	protected $serviceId;
	protected $metrix;
	protected $date;
	protected $granularity;

	CONST GRANULARITY_DAILY = 'daily';
	CONST GRANULARITY_MONTHLY = 'monthly';
	
	public function __construct($db, $inputData) {
		$this->db = $db;
		$this->serviceId = isset($inputData["serviceId"]) ? $inputData["serviceId"] : null;
		$this->metrix = isset($inputData["metrix"]) ? $inputData["metrix"] : null;
		$this->date = isset($inputData["date"]) ? $inputData["date"] : null;
		$this->granularity = isset($inputData["granularity"]) ? $inputData["granularity"] : null;
	}
	
	/**
	 * Get toal
	 * @return int total
	 */
	public function getTotal() {
		if (null == $this->serviceId || null == $this->metrix ) {
			throw new Exception("Client period missing some of input params.");
		}

		if ( null == $this->date ) {
			$date = array(
				'$gte' => '201601'
			);
			$format = "Ymd"; //default is daily
    	} 
    	else {
    		$format = $this->granularity == ClientPeriod::GRANULARITY_MONTHLY ? "Ym" : "Ymd"; //default is daily
        	$dt = date_create($this->date);
	    	$date = date_format($dt, $format);
    	}


       // db.daily.aggregate([{"$match":{"metadata.date":{"$gt":"20170501","$lt":"20170517"},"metadata.metrix":"sms","metadata.serviceId":"1111"}},{"$group":{"_id":null,"total":{"$sum":"$total"}}}])

    	try {

			$pipeline = array(
				array(
					'$match' => array(
						"metadata.date" => $date,
						'metadata.metrix' => $this->metrix,
				        'metadata.serviceId' => $this->serviceId
					)
				),
				array(
					'$group' => array(
				      "_id" => null,
				      "total" => array(
				        '$sum' => '$total'
				      )
				    )
				)			
			);

			$options = array(
			//	"_id" => 0
			);
			
			if ($this->granularity == ClientPeriod::GRANULARITY_MONTHLY) {
				$cursor =  $this->db->monthly->aggregate($pipeline, $options);
			} 
			else {
				$cursor =  $this->db->daily->aggregate($pipeline, $options);
			}
	    	

			$it = new \IteratorIterator($cursor);
			$it->rewind();

			$total = 0;
			while($doc = $it->current()) {
				if(isset($doc["total"])) {
					$total = $doc["total"];
					break;
				}
			    $it->next();
			}

		} catch(Exception $e) {
			$total = 0;
		}	

		return $total;
	}

}