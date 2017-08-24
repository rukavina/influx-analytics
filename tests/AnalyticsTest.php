<?php

use PHPUnit\Framework\TestCase;
use Vorbind\InfluxAnalytics\Connection;
use Vorbind\InfluxAnalytics\AnalyticsInterface;
use Vorbind\InfluxAnalytics\Analytics;
use Vorbind\InfluxAnalytics\Mapper\AnalyticsMapperInterface;
use Vorbind\InfluxAnalytics\Mapper\AnalyticsMapper;


use Vorbind\InfluxAnalytics\Exception\AnalyticsException;

class AnalyticsTest extends TestCase {

    protected static $db;

    public static function setUpBeforeClass() {
        $conn = new Connection('admin', 'adm1n', 'localhost', 8186);
        //$conn = new Connection();
        self::$db = $conn->getDatabase("news");

//        try {
    //            self::$db->query(sprintf('drop measurement "%s"', "campaign"));
//            self::$db->query(sprintf('drop measurement "%s"', "list"));
//            self::$db->query(sprintf('drop measurement "%s"', "contact"));
//            self::$db->query(sprintf('drop measurement "%s"', "sms"));
//        } catch (Exception $e) {
//            print("Exception measurement not exist..");
//        }
    }
    
    
    public function providerCampaigns() {
        $service = "d354fe67-87f2-4438-959f-65fde4622044";
        $data = [];
        
        for($i=0;$i<24;$i++) {
            $hh = $i < 10 ? "0".$i : $i;
            $data[] = ["campaign", json_encode(['status' => 'finish', 'service' => $service]), 1, "2017-08-01 $hh:01:11"];
        }
        
        for($i=0;$i<24;$i++) {
            $hh = $i < 10 ? "0".$i : $i;
            $data[] = ["campaign", json_encode(['status' => 'finish', 'service' => $service]), 10, "2017-08-02 $hh:01:11"];
        }
        
        for($i=0;$i<24;$i++) {
            $hh = $i < 10 ? "0".$i : $i;
            $data[] = ["campaign", json_encode(['status' => 'finish', 'service' => $service]), 100, "2017-08-03 $hh:01:11"];
        }
    
        for($i=0;$i<24;$i++) {
            $hh = $i < 10 ? "0".$i : $i;
            $data[] = ["campaign", json_encode(['status' => 'finish', 'service' => $service]), 1000, "2017-08-04 $hh:01:11"];
        }
        
        return $data;
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

        $data = [];

        //------------ campaigns -----------//
        $data[] = ["campaign", json_encode(['status' => 'finish', 'running_status' => 'idle', 'name' => 'january', 'service' => $service]), 1, "2017-01-01 00:01:11"];
        $data[] = ["campaign", json_encode(['status' => 'finish', 'running_status' => 'idle', 'name' => 'february', 'service' => $service]), 1, "2017-02-01 00:01:11"];
        $data[] = ["campaign", json_encode(['status' => 'finish', 'running_status' => 'idle', 'name' => 'martz', 'service' => $service]), 1, "2017-03-01 00:01:11"];
        $data[] = ["campaign", json_encode(['status' => 'finish', 'running_status' => 'idle', 'name' => 'april', 'service' => $service]), 1, "2017-04-01 00:01:11"];
        $data[] = ["campaign", json_encode(['status' => 'finish', 'running_status' => 'running', 'name' => 'may sun', 'service' => $service]), 1, "2017-05-01 00:01:11"];
        $data[] = ["campaign", json_encode(['status' => 'active', 'running_status' => 'paused', 'name' => 'may cloud', 'service' => $service]), 1, "2017-05-11 00:03:01"];
        $data[] = ["campaign", json_encode(['status' => 'active', 'running_status' => 'running', 'name' => 'may middle', 'service' => $service]), 1, "2017-05-12 00:03:01"];
        $data[] = ["campaign", json_encode(['status' => 'active', 'running_status' => 'idle', 'name' => 'jun', 'service' => $service]), 1, "2017-06-01 00:01:11"];
        $data[] = ["campaign", json_encode(['status' => 'active', 'running_status' => 'idle', 'name' => 'july', 'service' => $service]), 1, "2017-07-01 00:01:11"];

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
        $data = $this->getMonthData("sms", ['status' => 'sent', 'type' => 'easysms', 'service' => $service], $data, "07", 21, 10);

        $data = $this->getMonthData("sms", ['status' => 'sent', 'type' => 'scheduled', 'campaign' => $campaign1, 'service' => $service], $data, "01");
        $data = $this->getMonthData("sms", ['status' => 'sent', 'type' => 'scheduled', 'campaign' => $campaign2, 'service' => $service], $data, "02");
        $data = $this->getMonthData("sms", ['status' => 'sent', 'type' => 'scheduled', 'campaign' => $campaign3, 'service' => $service], $data, "03");
        $data = $this->getMonthData("sms", ['status' => 'sent', 'type' => 'scheduled', 'campaign' => $campaign4, 'service' => $service], $data, "04");
        $data = $this->getMonthData("sms", ['status' => 'sent', 'type' => 'scheduled', 'campaign' => $campaign5, 'service' => $service], $data, "05");
        $data = $this->getMonthData("sms", ['status' => 'sent', 'type' => 'scheduled', 'campaign' => $campaign6, 'service' => $service], $data, "06");
        $data = $this->getMonthData("sms", ['status' => 'sent', 'type' => 'scheduled', 'campaign' => $campaign7, 'service' => $service], $data, "07", 21, 10);

        //------------ contacts -----------//
        $data = $this->getMonthData("contact", ['status' => 'active', 'service' => $service], $data, "01");
        $data = $this->getMonthData("contact", ['status' => 'active', 'service' => $service], $data, "02");
        $data = $this->getMonthData("contact", ['status' => 'active', 'service' => $service], $data, "03");
        $data = $this->getMonthData("contact", ['status' => 'active', 'service' => $service], $data, "04");
        $data = $this->getMonthData("contact", ['status' => 'active', 'service' => $service], $data, "05");
        $data = $this->getMonthData("contact", ['status' => 'active', 'service' => $service], $data, "06");
        $data = $this->getMonthData("contact", ['status' => 'active', 'service' => $service], $data, "07", 21, 10);

        return $data;
    }

    public function providerTotalData() {
        $service = "d354fe67-87f2-4438-959f-65fde4622044";
        return [
                ["sms", "2017-06-01 00:00:00", "2017-08-25 23:59:59", json_encode(["service" => $service, "status" => "sent"]), "hourly"],
                ["sms", "2017-06-01 00:00:00", "2017-08-25 23:59:59", json_encode(["service" => $service, "status" => "sent"]), "daily"],
                ["sms", "2017-06-01 00:00:00", "2017-08-25 23:59:59", json_encode(["service" => $service, "status" => "sent"]), "weekly"]
        ];
    }

    public function providerTotal() {
        $service = "d354fe67-87f2-4438-959f-65fde4622044";
        return [
            //total    
                ["sms", "2017-01-01 00:00:00", "2017-08-25 23:59:59", json_encode(["service" => $service, "status" => "sent", "type" => "easysms"]), json_encode([])]
        ];
    }
    
    /**
     * //dataProvider providerData
     * @dataProvider providerCampaigns
     *  
     * @test
     */
    public function save($metric, $tags, $value, $utc, $rp = "years_5") {
        $data = null;
        try {
            /**
             * @var AnalyticsMapperInterface
             */
            $mapper = new AnalyticsMapper(self::$db);

            /**
             * @var AnalyticsInterface
             */
            $analytics = new Analytics($mapper);
            
            $data = $analytics->save($metric, json_decode($tags, true), $value, $utc, $rp);
            $this->assertNotEmpty($data);
            $this->assertTrue($data);
        } catch (AnalyticsException $e) {
            $data = null;
            $this->assertNotEmpty($data);
        }
    }

    /**
     * //dataProvider providerTotalData 
     * //test
     */
    public function getData($metric, $startDt, $endDt, $tags, $granularity) {
        $data = null;
        try {
            /**
             * @var AnalyticsMapperInterface
             */
            $mapper = new AnalyticsMapper(self::$db);

            /**
             * @var AnalyticsInterface
             */
            $analytics = new Analytics($mapper);
            
            $data = $analytics->getData("years_5", $metric, json_decode($tags, true), $granularity, $startDt, $endDt);
            print_r($data);
            $this->assertTrue(is_array($data));
        } catch (AnalyticsException $e) {
            echo $e->getMessage();
            $this->assertNotEmpty($data);
            return;
        }
    }

    /**
     * //dataProvider providerTotal 
     * //test
     */
    public function getTotal($metrix, $startDt, $endDt, $tags) {
        $total = null;
        try {
            /**
             * @var AnalyticsMapperInterface
             */
            $mapper = new AnalyticsMapper(self::$db);

            /**
             * @var AnalyticsInterface
             */
            $analytics = new Analytics($mapper);
            
            $total = $analytics->getTotal("years_5", $metrix, json_decode($tags, true), $startDt, $endDt);

            $this->assertTrue(is_integer($total));
            $this->assertGreaterThanOrEqual(0, $total);
            echo "@@@TOTAL[$startDt]-[$endDt][$metrix]:$total";
        } catch (AnalyticsException $e) {
            echo $e->getMessage();
            $this->assertNotEmpty($total);
            return;
        }
    }

    //-------- helper methods --------//

    protected function getMonthData($metrix, $tags, $data, $m, $nd = null, $nh = null) {
        $jd = 1;
        $daysInMonth = $m == "02" ? 28 : 30;

        while ($jd <= $daysInMonth) {
            $ih = 0;
            while ($ih <= 23) {

                $d = $jd < 10 ? "0" . $jd : $jd;
                $h = $ih < 10 ? "0" . $ih : $ih;

                if ("sms" == $metrix) {
                    $tags["status"] = rand(0, 1) ? "sent" : "delivered";
                }

                $item = [$metrix, json_encode($tags), rand(100, 600), "2017-$m-$d $h:01:00"];
                $data[] = $item;
                $ih++;

                if (isset($nd) && isset($nh)) {
                    if ($jd == $nd && $ih > $nh) {
                        return $data;
                    }
                }
            }
            $jd++;
        }
        return $data;
    }

}
