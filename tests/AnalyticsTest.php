<?php 

use PHPUnit\Framework\TestCase;
use Vorbind\InfluxAnalytics\Connection;
use Vorbind\InfluxAnalytics\Analytics;
use Vorbind\InfluxAnalytics\Exception\AnalyticsException;


class AnalyticsTest extends TestCase {  

  protected static $db;

  public static function setUpBeforeClass() {
    echo ">>> set up only once..";
    $conn = new Connection();
    self::$db = $conn->getDatabase("news");

    try {
      self::$db->query(sprintf('drop measurement "%s"', "campaign"));
      self::$db->query(sprintf('drop measurement "%s"', "list"));
      self::$db->query(sprintf('drop measurement "%s"', "contact"));
      self::$db->query(sprintf('drop measurement "%s"', "sms"));
    } catch (Exception $e) {
      print("Exception measurement not exist..");
    }
  }

  public function providerData($data) {
      $service = "d354fe67-87f2-4438-959f-65fde4622044";

      $campaign1 = "d354fe67-87f2-4438-959f-65fde4622111";
      $campaign2 = "d354fe67-87f2-4438-959f-65fde4622222";
      $campaign3 = "d354fe67-87f2-4438-959f-65fde4622333";
      $campaign4 = "d354fe67-87f2-4438-959f-65fde4622444";
      $campaign5 = "d354fe67-87f2-4438-959f-65fde4622555";
      $campaign6 = "d354fe67-87f2-4438-959f-65fde4622666";
      $campaign7 = "d354fe67-87f2-4438-959f-65fde4622777";
      $campaign8 = "d354fe67-87f2-4438-959f-65fde4622888";
      $campaign9 = "d354fe67-87f2-4438-959f-65fde4622999";

    
      $data = [];

      //------------ campaigns -----------//
      $data[] = ["campaign", json_encode(['status' => 'finish','running_status' => 'idle', 'name' =>'january', 'service' => $service]), 1, "2017-01-01 00:01:11"];
      $data[] = ["campaign", json_encode(['status' => 'finish','running_status' => 'idle', 'name' =>'february', 'service' => $service]), 1, "2017-02-01 00:01:11"];
      $data[] = ["campaign", json_encode(['status' => 'finish','running_status' => 'idle', 'name' =>'martz', 'service' => $service]), 1, "2017-03-01 00:01:11"];
      $data[] = ["campaign", json_encode(['status' => 'finish','running_status' => 'idle', 'name' =>'april', 'service' => $service]), 1, "2017-04-01 00:01:11"];
      $data[] = ["campaign", json_encode(['status' => 'finish','running_status' => 'running','name' => 'may sun', 'service' => $service]), 1, "2017-05-01 00:01:11"];
      $data[] = ["campaign", json_encode(['status' => 'active','running_status' => 'paused','name' => 'may cloud', 'service' => $service]), 1, "2017-05-11 00:03:01"];
      $data[] = ["campaign", json_encode(['status' => 'active','running_status' => 'running','name' => 'may middle', 'service' => $service]), 1, "2017-05-12 00:03:01"];
      $data[] = ["campaign", json_encode(['status' => 'active','running_status' => 'idle','name' => 'jun', 'service' => $service]), 1, "2017-06-01 00:01:11"];
      $data[] = ["campaign", json_encode(['status' => 'active','running_status' => 'idle','name' => 'july', 'service' => $service]), 1, "2017-07-01 00:01:11"];
      $data[] = ["campaign", json_encode(['status' => 'active','running_status' => 'idle','name' => 'avgust', 'service' => $service]), 1, "2017-08-01 00:01:11"];
      $data[] = ["campaign", json_encode(['status' => 'active','running_status' => 'idle','name' => 'september', 'service' => $service]), 1, "2017-09-01 00:01:11"];

            
      //------------ lists -----------//
      $data[] = ["list", json_encode(['status' => 'active', 'service' => $service]), 1, "2017-01-01 11:03:23"];
      $data[] = ["list", json_encode(['status' => 'active', 'service' => $service]), 1, "2017-03-11 14:13:41"];
      $data[] = ["list", json_encode(['status' => 'active', 'service' => $service]), 1, "2017-04-02 08:23:11"];
      $data[] = ["list", json_encode(['status' => 'active', 'service' => $service]), 1, "2017-05-02 08:23:11"];
      $data[] = ["list", json_encode(['status' => 'active', 'service' => $service]), 1, "2017-06-02 08:23:11"]; 
      
      //------------ smss -----------//
      $data = $this->getMonthData("sms", ['status' => 'sent', 'type' => 'easysms', 'service' => $service], $data, "01");
      $data = $this->getMonthData("sms", ['status' => 'sent', 'type' => 'easysms', 'service' => $service], $data, "02");
      $data = $this->getMonthData("sms", ['status' => 'sent', 'type' => 'easysms', 'service' => $service], $data, "03");
      $data = $this->getMonthData("sms", ['status' => 'sent', 'type' => 'easysms', 'service' => $service], $data, "04");
      $data = $this->getMonthData("sms", ['status' => 'sent', 'type' => 'easysms', 'service' => $service], $data, "05");
      $data = $this->getMonthData("sms", ['status' => 'sent', 'type' => 'easysms', 'service' => $service], $data, "06");
      $data = $this->getMonthData("sms", ['status' => 'sent', 'type' => 'easysms', 'service' => $service], $data, "07");
      $data = $this->getMonthData("sms", ['status' => 'sent', 'type' => 'easysms', 'service' => $service], $data, "08");
      $data = $this->getMonthData("sms", ['status' => 'sent', 'type' => 'easysms', 'service' => $service], $data, "09");
      
      $data = $this->getMonthData("sms", ['status' => 'sent','type' => 'scheduled', 'campaign' => $campaign1, 'service' => $service], $data, "01");
      $data = $this->getMonthData("sms", ['status' => 'sent','type' => 'scheduled', 'campaign' => $campaign2, 'service' => $service], $data, "02");
      $data = $this->getMonthData("sms", ['status' => 'sent','type' => 'scheduled', 'campaign' => $campaign3, 'service' => $service], $data, "03");
      $data = $this->getMonthData("sms", ['status' => 'sent','type' => 'scheduled', 'campaign' => $campaign4, 'service' => $service], $data, "04");
      $data = $this->getMonthData("sms", ['status' => 'sent','type' => 'scheduled', 'campaign' => $campaign5, 'service' => $service], $data, "05");
      $data = $this->getMonthData("sms", ['status' => 'sent','type' => 'scheduled', 'campaign' => $campaign6, 'service' => $service], $data, "06");
      $data = $this->getMonthData("sms", ['status' => 'sent','type' => 'scheduled', 'campaign' => $campaign7, 'service' => $service], $data, "07");
      $data = $this->getMonthData("sms", ['status' => 'sent','type' => 'scheduled', 'campaign' => $campaign8, 'service' => $service], $data, "08");
      $data = $this->getMonthData("sms", ['status' => 'sent','type' => 'scheduled', 'campaign' => $campaign9, 'service' => $service], $data, "09");

      //------------ contacts -----------//
      $data = $this->getMonthData("contact", ['status' => 'active', 'service' => $service], $data, "01");
      $data = $this->getMonthData("contact", ['status' => 'active', 'service' => $service], $data, "02");
      $data = $this->getMonthData("contact", ['status' => 'active', 'service' => $service], $data, "03");
      $data = $this->getMonthData("contact", ['status' => 'active', 'service' => $service], $data, "04");
      $data = $this->getMonthData("contact", ['status' => 'active', 'service' => $service], $data, "05");
      $data = $this->getMonthData("contact", ['status' => 'active', 'service' => $service], $data, "06");
      $data = $this->getMonthData("contact", ['status' => 'active', 'service' => $service], $data, "07");
      $data = $this->getMonthData("contact", ['status' => 'active', 'service' => $service], $data, "08");
      $data = $this->getMonthData("contact", ['status' => 'active', 'service' => $service], $data, "09");
      
      return $data;
  }
  
  /**
   * @dataProvider providerData 
   * @test
   */
  public function save($metrix, $tags, $value, $utc) {
    try {
      $analytics = new Analytics();
      $data = $analytics->save(self::$db, $metrix, json_decode($tags, true), $value, $utc); 
    } catch(AnalyticsException $e) {
      $data = null;
      $this->assertNotEmpty($data);
      return;
    }
    $this->assertTrue($data);
  }

  //-------- helper methods --------//
  
  protected function getMonthData($metrix, $tags, $data, $m) {
    $limit = rand(10, 60);
    $i = 0;
    // mart
    while($i <= $limit) {
      $d = rand(1,31);
      $h = rand(0,23);
      $mm = rand(0,59);
      $s = rand(0, 59);
      
      $d = $d < 10 ? "0" . $d : $d;
      $h = $h < 10 ? "0" . $h : $h;
      $mm = $mm < 10 ? "0" . $mm : $mm;
      $s = $s < 10 ? "0" . $s : $s;
      
      if ("sms" == $metrix) {
        $tags["status"] = rand(0,1) ? "sent" : "delivered";
      }

      $item = [$metrix, json_encode($tags), 1, "2017-$m-$d $h:$mm:$s"];
      $data[] = $item;
      $i++;
    }
    return $data;
  }
  
  
}