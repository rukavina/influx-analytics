<?php 

use PHPUnit\Framework\TestCase;
use Vorbind\InfluxAnalytics\Connection;
use Vorbind\InfluxAnalytics\Analytics;
use Vorbind\InfluxAnalytics\Exception\AnalyticsException;


class AnalyticsRealTest extends TestCase {  

  protected static $db;

  public static function setUpBeforeClass() {
    $conn = new Connection('zeka','z3k0','localhost',8186);
    //$conn = new Connection();
    self::$db = $conn->getDatabase("news");
  }

  public function providerData($data) {
      $easySentValue = rand(50,60);
      $easyDeliveredValue = rand(20,30);

      $campaignSentValue = rand(70,80);
      $campaignDeliveredValue = rand(50, 60);

      return [
      	["sms", json_encode(['status' => 'sent', 'type' => 'easysms', 'service' => "d354fe67-87f2-4438-959f-65fde4622044"]), $easySentValue, null],
        ["sms", json_encode(['status' => 'delivered', 'type' => 'easysms', 'service' => "d354fe67-87f2-4438-959f-65fde4622044"]), $easyDeliveredValue, null],
        ["sms", json_encode(['status' => 'sent', 'type' => 'campaign', 'service' => "d354fe67-87f2-4438-959f-65fde4622044"]), $campaignSentValue, null],
        ["sms", json_encode(['status' => 'delivered', 'type' => 'campaign', 'service' => "d354fe67-87f2-4438-959f-65fde4622044"]), $campaignDeliveredValue, null]
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
