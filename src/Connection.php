<?php 

namespace Vorbind\InfluxAnalytics;

 use InfluxDB\Client;
 use InfluxDB\Database;
 use Vorbind\InfluxAnalytics\Exception\AnalyticsException;

/**
*  Analytics
*
*  Use this section to define what this class is doing, the PHPDocumentator will use this
*  to automatically generate an API documentation using this information.
*
*  @author sasa.rajkovic
*/
class Connection {

    private $db;
    private $client;
    private $host;
    private $port;

//    public function __construct($host = 'localhost', $port = '8088') {
    public function __construct($host = 'localhost', $port = '8086') {
      $this->host = $host;
      $this->port = $port;
    }

    public function getDatabase($name) {
      try {
    
        if (!isset($name)) {
          throw InvalidArgumentException::invalidType('"db name" driver option', $name, 'string');
        }

        if (null == $this->client) {
          $this->client = new \InfluxDB\Client($this->host, $this->port);
        }

        if (!isset($this->dbs[$name])) {
          $db = $this->client->selectDB($name);
          if(!$db->exists()) {
            $db->create(null, false);
          }
          $this->dbs[$name] = $db;
        }
      } catch(Exception $e) {
          error_log("Errrorrrr...");
          throw new AnalyticsException("Connecting influx db faild", 0, $e);
      }
      return $this->dbs[$name];
    }
}
