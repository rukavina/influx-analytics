<?php 

namespace Vorbind\InfluxAnalytics;

use InfluxDB\Database;
use InfluxDB\Point;

use Vorbind\InfluxAnalytics\Exeception\AnalyticsException;


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
     * @param  string $metrix    Metrix
     * @param  array $tags       Tags
     * @param  string $date     Datetime
     */
    public function save($db, $metrix, $tags = array(), $value = 1, $date) {
      try {
          $command =  isset($date) ? " -d '" . $this->normalizeUTC($date) . "'" : "";
          $timeNs = exec("date $command +%s%N"); // Time precision is in nanaoseconds
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
      } catch(Exception $e) {
          throw new AnalyticsException("Error saving analytics data", 0, $e);
      }
	    return $result;
    } 

    protected function normalizeUTC($date) {
      $parts = explode(" ", $date);
      if(!is_array($parts) || count($parts) != 2) {
          throw new AnalyticsNormalizeException("Error normalize date, wrong format[$date]");
      }
      return $parts[0] . "T" . $parts[1] . "Z";
    }   
}