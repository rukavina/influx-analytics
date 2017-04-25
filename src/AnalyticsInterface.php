<?php 

namespace Vorbind\InfluxAnalytics;

interface InfluxAnalyticsInterface {

	public function save($db, $serviceId, $metrix, $utcDt);
	
} 