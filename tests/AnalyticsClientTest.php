<?php

use PHPUnit\Framework\TestCase;
use Vorbind\InfluxAnalytics\Connection;
use Vorbind\InfluxAnalytics\Client\AnalyticsClient;
use Vorbind\InfluxAnalytics\Client\AnalyticsEntity;
use Vorbind\InfluxAnalytics\Exception\AnalyticsException;

class AnalyticsClientTest extends TestCase {

    protected static $db;

    public static function setUpBeforeClass() {
        $conn = new Connection('zeka', 'z3k0', 'localhost', 8186);
        // $conn = new Connection();
        self::$db = $conn->getDatabase("news");
    }

    public function providerData() {
        $service = "d354fe67-87f2-4438-959f-65fde4622044";
        return [
                ["sms", "2017-06-01 00:00:00", "2017-06-02 23:59:59", json_encode(["service" => $service, "status" => "sent"]), "hourly"],
                ["sms", "2017-06-01 00:00:00", "2017-06-02 23:59:59", json_encode(["service" => $service, "status" => "sent"]), "daily"],
                ["sms", "2017-06-01 00:00:00", "2017-06-02 23:59:59", json_encode(["service" => $service, "status" => "sent"]), "weekly"]
        ];
    }

    public function providerTotalData() {
        $service = "d354fe67-87f2-4438-959f-65fde4622044";
        return [
            //total    
                ["sms", "2017-01-01 00:00:00", "2017-08-02 23:59:59", json_encode(["service" => $service, "status" => "sent", "type" => "easysms"]), json_encode([])]
        ];
    }

    /**
     * @dataProvider providerData 
     * @test
     */
    public function getData($metrix, $startDt, $endDt, $tags, $granularity) {
        $data = null;
        try {
            $entity = new AnalyticsEntity([
                "rp" => "years_5",
                "metric" => $metrix,
                "tags" => json_decode($tags, true)
            ]);

            /**
             * @var AnalyticsClient
             */
            $client = new AnalyticsClient(self::$db, $entity);
            $data = $client->getData($granularity, $startDt, $endDt);
            print_r($data);
            $this->assertTrue(is_array($data));
        } catch (AnalyticsException $e) {
            echo $e->getMessage();
            $this->assertNotEmpty($data);
            return;
        }
    }

    /**
     * @dataProvider providerTotalData 
     * @test
     */
    public function getTotal($metrix, $startDt, $endDt, $tags) {
        $total = null;
        try {
            /**
             * @var AnalyticsEntity
             */
            $entity = new AnalyticsEntity([
                "rp" => "years_5",
                "metric" => $metrix,
                "tags" => json_decode($tags, true)
            ]);
            
            /**
             * @var AnalyticsClient
             */
            $client = new AnalyticsClient(self::$db, $entity);
            $total = $client->getTotal($startDt,$endDt);
            
            $this->assertTrue(is_integer($total));
            $this->assertGreaterThanOrEqual(0, $total);
            echo "@@@TOTAL[$startDt]-[$endDt][$metrix]:$total";
        } catch (AnalyticsException $e) {
            echo $e->getMessage();
            $this->assertNotEmpty($total);
            return;
        }
    }

}
