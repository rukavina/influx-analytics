<?php 

namespace Vorbind\InfluxAnalytics;

use Vorbind\InfluxAnalytics\Exception\AnalyticsException;

/**
 * Class AnalyticsTrait
 */
trait AnalyticsTrait {
    
    /**
	 * Get timezone offset
	 * @param  string $origin_tz
	 * @return int offset in hours
	 */
	public function getTimezoneHourOffset($origin_tz = 'UTC') {
		$remote_tz = 'UTC';
		if($origin_tz === 'UTC') {
	        return 0 . 'h';
	    }
	    $origin_dtz = new \DateTimeZone($origin_tz);
	    $remote_dtz = new \DateTimeZone($remote_tz);
	    $origin_dt = new \DateTime("now", $origin_dtz);
	    $remote_dt = new \DateTime("now", $remote_dtz);

	    $offset = $origin_dtz->getOffset($origin_dt) - $remote_dtz->getOffset($remote_dt);
	    return $offset / 3600  . 'h';
	}

	/**
	 * Normalize UTC 
	 * @param  string $date 
	 * @return string
	 */
	public function normalizeUTC($date) {
		$parts = explode(" ", $date);
        if(!is_array($parts) || count($parts) != 2) {
            throw new AnalyticsNormalizeException("Error normalize date, wrong format[$date]");
        }
        return $parts[0] . "T" . $parts[1] . "Z";
	}

	/**
	 * Find key by sub value
	 * @param  string $needle   
	 * @param  array $haystack 
	 * @return string          
	 */
	public function arrayMultiSearch($needle,$haystack) {
		foreach ($haystack as $key=>$data) {
			if (in_array($needle,$data)) {
				return $key;
			}
		}
		return false;
	} 
}