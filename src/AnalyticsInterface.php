<?php 

namespace Vorbind\InfluxAnalytics;

interface AnalyticsInterface {

	public function save($db, $serviceId, $metrix, $tags, $value, $utc);
	
} 