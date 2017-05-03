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
          ["d354fe67-87f2-4438-959f-65fde4622044", "sms", "2017-03-04T01:12:12Z", json_encode(array("status"=>"send"))],
      ];
  }

  public function providerTotalData() {
      return [          
          //total    
          ["d354fe67-87f2-4438-959f-65fde4622044", "sms", json_encode(array())],
      ];
  }

  /**
   * @dataProvider providerTotalByDateData 
   * @test
   */
  public function getTotalByDate($service, $metrix, $date, $tags) {
    $inputData = [
      "serviceId" => $service,
      "metrix"  => $metrix,
      "date"   => $date,
      "tags"   => json_decode($tags, true)
    ];
    $factory = new ClientFactory();
    $client = $factory->create(self::$db, 'total', $inputData);
    $total = $client->getTotal();

    $this->assertTrue(is_integer($total));
    //$this->assertEquals(36, $total);
    $this->assertGreaterThan(0, $total);
    echo "TOTAL[$date]:";
    var_dump($total);
  }
  
  /**
   * @dataProvider providerTotalData 
   * @test
   */
  public function getTotal($service, $metrix, $tags) {
    $inputData = [
      "serviceId" => $service,
      "metrix"  => $metrix,    
      "tags"   => json_decode($tags, true),
    ];
    $factory = new ClientFactory();
    $client = $factory->create(self::$db, 'total', $inputData);
    $total = $client->getTotal();

    $this->assertTrue(is_integer($total));
    $this->assertGreaterThan(0, $total);
    echo "TOTAL:";
    var_dump($total);
  }  
  
}