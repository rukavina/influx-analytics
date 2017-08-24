<?php

include "../../vendor/autoload.php";

use Vorbind\InfluxAnalytics\Import\ImportConfigReader;
use Vorbind\InfluxAnalytics\Adapter\MysqlDatabaseAdapter;
use Vorbind\InfluxAnalytics\Adapter\InfluxDatabaseAdapter;
use Vorbind\InfluxAnalytics\Mapper\AnalyticsMapper;
use Vorbind\InfluxAnalytics\Mapper\ImportMysqlMapper;
use Vorbind\InfluxAnalytics\Analytics;
use Vorbind\InfluxAnalytics\Import\ImportAnalytics;

$shortopts = "c:";  // Required value
$longopts  = array(
    "config:"     // Required value
);
$options = getopt($shortopts, $longopts);

if (count($argv) <= 1){
    print("ERROR: Config file is missing ex:  --config '/var/local/config.json' ");
    exit;
}

try {
    $reader = new ImportConfigReader($options["config"]);
    $mysqlAdapter = new MysqlDatabaseAdapter($reader);
    $influxAdapter = new InfluxDatabaseAdapter($reader);
    $analyticsMapper = new AnalyticsMapper($influxAdapter->getDatabaseAdapter());
    $analytics = new Analytics($analyticsMapper);
    $importMapper = new ImportMysqlMapper($mysqlAdapter->getDatabaseAdapter());
            
    $import = new ImportAnalytics($importMapper, $analytics, $reader);
    $import->execute();
      
    echo "\n\nFinish importing data in influxdb :)";
} catch(Exception $e) {
    printf("ERROR:" . $e->getMessage());
}

