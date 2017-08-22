<?php 

namespace Vorbind\InfluxAnalytics;

interface AnalyticsInterface {

	public function save($db, $metrix, $tags, $value, $utc, $rp);
	
} 