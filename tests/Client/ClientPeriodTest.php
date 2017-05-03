<?php 

use PHPUnit\Framework\TestCase;
use Vorbind\InfluxAnalytics\Connection;
use Vorbind\InfluxAnalytics\Client\ClientFactory;
use Vorbind\InfluxAnalytics\Client\ClientPeriod;


class ClientPeriodTest extends TestCase {	

  protected static $db;

  public static function setUpBeforeClass() {
    $conn = new Connection();
    self::$db = $conn->getDatabase("news");
  }

  public function providerData() {
      return [          
          //["d354fe67-87f2-4438-959f-65fde4622044", "sms", "20170301", "20170530","hourly"],
          //["d354fe67-87f2-4438-959f-65fde4622044", "sms", "2017-04-01T01:12:12Z", "2017-04-30T01:12:12Z", "daily"],
          [
            "d354fe67-87f2-4438-959f-65fde4622044", 
            "sms", 
            "2017-01-01T00:00:00Z", 
            "2017-06-30T00:12:12Z", 
            json_encode(array("status" => "send")),
            "weekly"
          ],
          //["d354fe67-87f2-4438-959f-65fde4622044", "sms", "20170401", "20170401","minute"],
      ];
  }

  public function providerTotalData() {
      return [          
          //total    
          [
            "d354fe67-87f2-4438-959f-65fde4622044", 
            "sms",
            json_encode(array("status" => "send", "type" => "scheduled"))
          ],
      ];
  }
  
  /**
   * @dataProvider providerData 
   * @test
   */
  public function getData($service, $metrix, $startDt, $endDt, $tags, $granularity) {
  	$inputData = [
  		"serviceId" => $service,
  		"metrix" 	=> $metrix,
  		"startDt" 	=> $startDt,
      "endDt" 	=> $endDt,
      "tags" => json_decode($tags, true),
  		"granularity" => $granularity
  	];
    $factory = new ClientFactory();
    $client = $factory->create(self::$db, 'period', $inputData);
    $data = $client->getData();

    $this->assertTrue(is_array($data));
 	
 //	  print_r($data);
  }

  /**
   * @dataProvider providerTotalData 
   * @test
   */
  public function getTotal($service, $metrix, $tags) {
    $inputData = [
      "serviceId" => $service,
      "metrix"  => $metrix,
      "tags" => json_decode($tags, true)
    ];
    $factory = new ClientFactory();
    $client = $factory->create(self::$db, 'period', $inputData);
    $total = $client->getTotal();

    $this->assertTrue(is_integer($total));
    $this->assertGreaterThan(0, $total);

    //$this->assertEquals(36, $total);
  }  
  
}