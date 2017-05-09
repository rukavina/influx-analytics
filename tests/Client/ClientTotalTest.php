<?php 

use PHPUnit\Framework\TestCase;
use Vorbind\InfluxAnalytics\Connection;
use Vorbind\InfluxAnalytics\Client\ClientFactory;
use Vorbind\InfluxAnalytics\Client\ClientPeriod;


class ClientTotalTest extends TestCase {	

  protected static $db;

  public static function setUpBeforeClass() {
    $conn = new Connection();
    self::$db = $conn->getDatabase("news");
  }

  public function providerTotalByDateData() {
      return [          
          //total    
          //["d354fe67-87f2-4438-959f-65fde4622044", "campaign", "2017-03-04 01:12:12", json_encode(array("status"=>"active"))],
          //["d354fe67-87f2-4438-959f-65fde4622044", "list", "2017-03-04 01:12:12", json_encode(array("status"=>"active"))],
          //["d354fe67-87f2-4438-959f-65fde4622044", "contact", "2017-03-04 01:12:12", json_encode(array("status"=>"active"))],
          ["d354fe67-87f2-4438-959f-65fde4622044", "sms", "2017-06-02 23:59:59", json_encode([])]
      ];
  }

  public function providerTotalData() {
      return [          
          //total    
          ["d354fe67-87f2-4438-959f-65fde4622044", "campaign", json_encode([])],
          ["d354fe67-87f2-4438-959f-65fde4622044", "list", json_encode([])],
          ["d354fe67-87f2-4438-959f-65fde4622044", "contact", json_encode([])],
          ["d354fe67-87f2-4438-959f-65fde4622044", "sms", json_encode([])]
      ];
  }

  /**
   * @dataProvider providerTotalByDateData 
   * @test
   */
  public function getTotalByDate($service, $metrix, $date, $tags) {
    $inputData = [
      "service" => $service,
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
  }
  
  /**
   * @dataProvider providerTotalData 
   * @test
   */
  public function getTotal($service, $metrix, $tags) {
    $inputData = [
      "service" => $service,
      "metrix"  => $metrix,    
      "tags"   => json_decode($tags, true),
    ];
    $factory = new ClientFactory();
    $client = $factory->create(self::$db, 'total', $inputData);
    $total = $client->getTotal();

    $this->assertTrue(is_integer($total));
    $this->assertGreaterThanOrEqual(0, $total);
    echo "@@@ TOTAL[$metrix]:$total";
  }  
  
}