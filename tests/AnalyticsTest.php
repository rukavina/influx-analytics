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
      self::$db->query(sprintf('drop measurement "%s"', "sms"));
      self::$db->query(sprintf('drop measurement "%s"', "contact"));
    } catch (Exception $e) {
      print("Exception measurement not exist..");
    }
  }

  public function providerData($data) {
      $data = [];
      //january
      $data = $this->getMonthData("contact", $data, array(), "01");
      $data = $this->getMonthData("sms", $data, array('status' => 'send','creator' => 'easysms'), "02");
      //february
      $data = $this->getMonthData("contact", $data, array(), "02");
      $data = $this->getMonthData("sms", $data, array('status' => 'send','creator' => 'easysms'), "02");
      //mart
      $data = $this->getMonthData("contact", $data, array(), "03");
      $data = $this->getMonthData("sms", $data, array('status' => 'send','creator' => 'easysms'), "03");
      //april
      $data = $this->getMonthData("contact", $data, array(), "04");
      $data = $this->getMonthData("sms", $data, array('status' => 'send','creator' => 'scheduled'), "04");
      //may
      $data = $this->getMonthData("contact", $data, array(), "05");
      $data = $this->getMonthData("sms", $data, array('status' => 'send','creator' => 'scheduled'), "04");
      //jun
      $data = $this->getMonthData("contact", $data, array(), "06");
      $data = $this->getMonthData("sms", $data, array('status' => 'send','creator' => 'scheduled'), "04");

      return $data;
  }
  
  /**
   * @dataProvider providerData 
   * @test
   */
  public function save($service, $metrix, $tags, $utc) {
    $analytics = new Analytics();
    $data = $analytics->save(self::$db, $service, $metrix, json_decode($tags, true), $utc); 
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

      $item = ["d354fe67-87f2-4438-959f-65fde4622044", $metrix, json_encode($tags), "2017-$m-$d $h:$mm:$s"];
      $data[] = $item;
      $i++;
    }
    return $data;
  }
  
  
}