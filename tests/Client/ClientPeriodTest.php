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
          ["d354fe67-87f2-4438-959f-65fde4622044", "sms", "2017-04-01 01:12:12", "2017-04-30 01:12:12","daily"],
          //["d354fe67-87f2-4438-959f-65fde4622044", "sms", "20170401", "20170401","minute"],
      ];
  }

  public function providerTotalData() {
      return [          
          //total    
          ["d354fe67-87f2-4438-959f-65fde4622044", "sms", "2017-03-01 01:12:12", "2017-04-17 01:12:12","daily"],
      ];
  }
  
  /**
   * @dataProvider providerData 
   * //test
   */
  public function getData($service, $metrix, $startDt, $endDt, $granularity) {
  	// $inputData = [
  	// 	"serviceId" => $service,
  	// 	"metrix" 	=> $metrix,
  	// 	"startDt" 	=> $startDt,
  	// 	"endDt" 	=> $endDt,
  	// 	"granularity" => $granularity
  	// ];
   //  $factory = new ClientFactory();
   //  $client = $factory->create(self::$db, 'period', $inputData);
   //  $data = $client->getData();

   //  $this->assertTrue(is_array($data));
 	
 	  //print_r($data);
  }

  /**
   * @dataProvider providerTotalData 
   * //test
   */
  public function getTotal($service, $metrix, $startDt, $endDt, $granularity) {
    // $inputData = [
    //   "serviceId" => $service,
    //   "metrix"  => $metrix,
    //   "startDt"   => $startDt,
    //   "endDt"   => $endDt,
    //   "granularity"   => $granularity,
    // ];
    // $factory = new ClientFactory();
    // $client = $factory->create(self::$db, 'period', $inputData);
    // $total = $client->getTotal();

    // $this->assertTrue(is_integer($total));
    // $this->assertEquals(36, $total);
  }  
  
}