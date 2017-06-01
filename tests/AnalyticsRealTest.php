<?php 

use PHPUnit\Framework\TestCase;
use Vorbind\InfluxAnalytics\Connection;
use Vorbind\InfluxAnalytics\Analytics;
use Vorbind\InfluxAnalytics\Exception\AnalyticsException;


class AnalyticsRealTest extends TestCase {  

  protected static $db;

  public static function setUpBeforeClass() {
    echo ">>> set up only once..";
    $conn = new Connection('zeka','z3k0', 'localhost', 8186);
    self::$db = $conn->getDatabase("news");
  }

  public function providerData($data) {
      return [
      	["sms", json_encode(['status' => 'sent', 'type' => 'easysms', 'service' => "d354fe67-87f2-4438-959f-65fde4622044"]), 1, null]
  	  ];
  }
  
  /**
   * @dataProvider providerData 
   * @test
   */
  public function save($metrix, $tags, $value, $utc) {
    $data = null;
    try {
      $analytics = new Analytics();
      $data = $analytics->save(self::$db, $metrix, json_decode($tags, true), $value, $utc);
      $this->assertNotEmpty($data);
      $this->assertTrue($data); 
    } catch(AnalyticsException $e) {
      $data = null;
      $this->assertNotEmpty($data);
    }
  }
}