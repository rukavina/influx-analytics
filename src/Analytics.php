<?php 

namespace Vorbind\InfluxAnalytics;

use InfluxDB\Database;
use InfluxDB\Point;


/**
*  InfluxAnalytics
*
*  Use this section to define what this class is doing, the PHPDocumentator will use this
*  to automatically generate an API documentation using this information.
*
*  @author sasa.rajkovic
*/
class Analytics implements AnalyticsInterface {

    /**
     * Save analytics
     * 
     * @param  InfluxDB\Database $db Mongo db
     * @param  string $service Service 
     * @param  string $metrix    Metrix
     * @param  array $tags       Tags
     * @param  string $date     Datetime
     */
    public function save($db, $serviceId, $metrix, $tags = array(), $value = 1, $date) {
      	//curl -i -XPOST 'http://localhost:8086/write?db=news' --data-binary 'sms,status=send,creator=scheduled service=1234-1234-1234-1234 value=1 1434055562000000000'   
        
        $command =  isset($date) ? " -d '" . $this->normalizeUTC($date) . "'" : "";
        $timeNs = exec("date $command +%s%N"); // Time precision is in nanaoseconds
        $tags['service'] = $serviceId;
        $fields = array();
        
        $points = array(
			new Point(
				$metrix,
				$value, // value
				$tags, // array('status' => 'send','type' => 'scheduled','campaign' => 'may')
				$fields, 
				$timeNs
			)
		);	    

		// we are writing a nanosecond precision
		$result = $db->writePoints($points, Database::PRECISION_NANOSECONDS);
		return $result;
    } 

    protected function normalizeUTC($date) {
        $parts = explode(" ", $date);
        if(!is_array($parts) || count($parts) != 2) {
            throw new Exeception("Wrong date format[$date]");
        }
        return $parts[0] . "T" . $parts[1] . "Z";
    }   
}