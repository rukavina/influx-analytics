<?php 

use PHPUnit\Framework\TestCase;
use Vorbind\InfluxAnalytics\Connection;
use Vorbind\InfluxAnalytics\Client\ClientFactory;
use Vorbind\InfluxAnalytics\Client\ClientPeriod;
use Vorbind\InfluxAnalytics\Exception\AnalyticsException;

class ClientTotalTest extends TestCase {  

  protected static $db;

  public static function setUpBeforeClass() {
    $conn = new Connection('zeka','z3k0','localhost',8186); 
    // $conn = new Connection();
    self::$db = $conn->getDatabase("news");
  }

  public function providerTotalByDateData() {
      $service = "d354fe67-87f2-4438-959f-65fde4622044";
      return [          
          ["campaign", "2017-06-02 23:59:59", json_encode(["service" => $service])],
          ["list", "2017-06-02 23:59:59", json_encode(["service" => $service])],
          ["contact", "2017-06-02 23:59:59", json_encode(["service" => $service])],
          ["sms", "2017-06-02 23:59:59", json_encode(["service" => $service])]
      ];
  }

  public function providerTotalData() {
      $service = "d354fe67-87f2-4438-959f-65fde4622044";
      return [          
          //total    
          ["campaign", json_encode(["service" => $service])],
          ["list", json_encode(["service" => $service])],
          ["contact", json_encode(["service" => $service])],
          ["sms", json_encode(["service" => $service])]
      ];
  }

  /**
   * @dataProvider providerTotalByDateData 
   * @test
   */
  public function getTotalByDate($metrix, $date, $tags) {
    $total = null;
    try {
      $inputData = [
        "metrix"  => $metrix,
        "date"   => $date,
        "tags"   => json_decode($tags, true)
      ];
      $factory = new ClientFactory();
      $client = $factory->create(self::$db, 'total', $inputData);
      $total = $client->getTotal();

      $this->assertTrue(is_integer($total));
      $this->assertGreaterThanOrEqual(0, $total);
      echo "@@@ TOTAL[$date][$metrix]:$total";
    } catch (AnalyticsException $e) {
      $this->assertNotEmpty($total);
      return;
    }
  }
  
  /**
   * @dataProvider providerTotalData 
   * @test
   */
  public function getTotal($metrix, $tags) {
    $total = null;
    try {
      $inputData = [
        "metrix"  => $metrix,    
        "tags"   => json_decode($tags, true),
      ];
      $factory = new ClientFactory();
      $client = $factory->create(self::$db, 'total', $inputData);
      $total = $client->getTotal();

      $this->assertTrue(is_integer($total));
      $this->assertGreaterThanOrEqual(0, $total);
      echo "@@@ TOTAL[$metrix]:$total";
    } catch (AnalyticsException $e) {
      $this->assertNotEmpty($total);
      return;
    } 
  }   
}