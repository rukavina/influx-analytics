<?php 

namespace Vorbind\InfluxAnalytics;

interface AnalyticsInterface {

	public function save($db, $service, $metrix, $tags, $value, $utc);
	
} 