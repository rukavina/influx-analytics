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
    self::$db->daily->drop();
    self::$db->monthly->drop();
  }

  public function providerAllocate() {
    $data = [];
    //february
    $data = $this->allocate("contact", $data, "02");
    $data = $this->allocate("sms", $data, "02");
    //mart
    $data = $this->allocate("contact", $data, "03");
    $data = $this->allocate("sms", $data, "03");
    //april
    $data = $this->allocate("contact", $data, "04");
    $data = $this->allocate("sms", $data, "04");

    return $data;
  }

  public function providerData($data) {
      $data = [];
      //february
      $data = $this->getMonthData("contact", $data, "02");
      $data = $this->getMonthData("sms", $data, "02");
      //mart
      $data = $this->getMonthData("contact", $data, "03");
      $data = $this->getMonthData("sms", $data, "03");
      //april
      $data = $this->getMonthData("contact", $data, "04");
      $data = $this->getMonthData("sms", $data, "04");

      return $data;
  }
  
  /**
   * @test
   * @dataProvider providerAllocate 
   */
  public function preallocate($service, $metrix, $utc) {
    $analytics = new Analytics();
    $analytics->preallocate(self::$db, $service, $metrix, $utc); 
    $data = self::$db->daily->find();
    $this->assertTrue(is_object($data));
    $data = self::$db->monthly->find();
    $this->assertTrue(is_object($data));
  }
  
  /**
   * @depends preallocate
   * @dataProvider providerData 
   * @test
   */
  public function save($service, $metrix, $utc) {
    $analytics = new Analytics();
    $analytics->save(self::$db, $service, $metrix, $utc); 
    $data = self::$db->news->find();
    $this->assertTrue(is_object($data));
  }

  //-------- helper methods --------//
  
  protected function allocate($metrix, $data, $m) {
    $i = 1;
    // mart
    while($i <= 31) {
      $d = $i<10 ? "0" . $i : $i;
      $item = ["d354fe67-87f2-4438-959f-65fde4622044", $metrix, "2017-$m-$d 00:00:01"];
      $data[] = $item;
      $i++;
    }

    return $data;  
  }

  protected function getMonthData($metrix, $data, $m) {
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

      $item = ["d354fe67-87f2-4438-959f-65fde4622044", $metrix, "2017-$m-$d $h:$mm:$s"];
      $data[] = $item;
      $i++;
    }
    return $data;
  }
  
  
}