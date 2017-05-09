<?php 

use PHPUnit\Framework\TestCase;
use Vorbind\InfluxAnalytics\Connection;
use Vorbind\InfluxAnalytics\Analytics;

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
      $data = [];

      //campaigns
      $data[] = ["d354fe67-87f2-4438-959f-65fde4622044", "campaign", json_encode(['status' => 'active','name'=>'april']), 1, "2017-04-01 00:01:11"];
      $data[] = ["d354fe67-87f2-4438-959f-65fde4622044", "campaign", json_encode(['status' => 'active','name'=>'may']), 1, "2017-05-02 00:03:01"];
      $data[] = ["d354fe67-87f2-4438-959f-65fde4622044", "campaign", json_encode(['status' => 'active','name'=>'jun']), 1, "2017-06-01 00:01:11"];
            
      //lists
      $data[] = ["d354fe67-87f2-4438-959f-65fde4622044", "list", json_encode(['status' => 'active']), 1, "2017-01-01 11:03:23"];
      $data[] = ["d354fe67-87f2-4438-959f-65fde4622044", "list", json_encode(['status' => 'active']), 1, "2017-03-11 14:13:41"];
      $data[] = ["d354fe67-87f2-4438-959f-65fde4622044", "list", json_encode(['status' => 'active']), 1, "2017-04-02 08:23:11"];
      
      //smss
      $data = $this->getMonthData("sms", $data, ['status' => 'send','type' => 'easysms'], "01");
      $data = $this->getMonthData("sms", $data, ['status' => 'send','type' => 'easysms'], "02");
      $data = $this->getMonthData("sms", $data, ['status' => 'send','type' => 'easysms'], "03");
      $data = $this->getMonthData("sms", $data, ['status' => 'send','type' => 'scheduled','campaign' => "april"], "04");
      $data = $this->getMonthData("sms", $data, ['status' => 'send','type' => 'scheduled','campaign' => "may"], "05");
      $data = $this->getMonthData("sms", $data, ['status' => 'send','type' => 'scheduled','campaign' => "jun"], "06");

      //contacts
      $data = $this->getMonthData("contact", $data, ['status' => 'active'], "01");
      $data = $this->getMonthData("contact", $data, ['status' => 'active'], "02");
      $data = $this->getMonthData("contact", $data, ['status' => 'active'], "03");
      $data = $this->getMonthData("contact", $data, ['status' => 'active'], "04");
      $data = $this->getMonthData("contact", $data, ['status' => 'active'], "05");
      $data = $this->getMonthData("contact", $data, ['status' => 'active'], "06");
      
  

      return $data;
  }
  
  /**
   * @dataProvider providerData 
   * @test
   */
  public function save($service, $metrix, $tags, $value, $utc) {
    $analytics = new Analytics();
    $data = $analytics->save(self::$db, $service, $metrix, json_decode($tags, true), $value, $utc); 
    $this->assertTrue($data);
  }

  //-------- helper methods --------//
  
  protected function getMonthData($metrix, $data, $tags, $m) {
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
      
      $item = ["d354fe67-87f2-4438-959f-65fde4622044", $metrix, json_encode($tags), 1, "2017-$m-$d $h:$mm:$s"];
      $data[] = $item;
      $i++;
    }
    return $data;
  }
  
  
}