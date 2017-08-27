#!/bin/bash
cd /var/www/news-api/vendor/influx-analytics/
../../phpunit/phpunit/phpunit tests/AnalyticsTest.php
