<?php
/*
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
 * "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
 * LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR
 * A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT
 * OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL,
 * SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT
 * LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE,
 * DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY
 * THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
 * (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE
 * OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 *
 * This software consists of voluntary contributions made by many individuals
 * and is licensed under the MIT license.
 */

namespace Vorbind\InfluxAnalytics;

use InfluxDB\Database;
use InfluxDB\Point;
use Vorbind\InfluxAnalytics\Exeception\AnalyticsException;

/**
 *  InfluxAnalytics
 *
 *  Use this section to define what this class is doing, the PHPDocumentator will use this
 *  to automatically generate an API documentation using this information.
 *
 *  @author sasa.rajkovic
 */
class Analytics implements AnalyticsInterface {

    use \Vorbind\InfluxAnalytics\AnalyticsTrait;

    /**
     * Save analytics
     *     
     * @param InfluxDB\Database $db
     * @param string $metric
     * @param array $tags
     * @param int $value
     * @param string $date
     * @param string $rp
     * @return void
     * @throws AnalyticsException
     */
    public function save($db, $metric, $tags = array(), $value = 1, $date = null, $rp = null) {
        try {
            $command = isset($date) ? " -d '" . $this->normalizeUTC($date) . "'" : "";
            // Time precision is in nanaoseconds
            $timeNs = exec("date $command +%s%N"); 
            $fields = array();

            $points = array(
                new Point(
                        $metric, 
                        $value, 
                        $tags, 
                        $fields, 
                        $timeNs
                )
            );
            return $db->writePoints($points, Database::PRECISION_NANOSECONDS, $rp);
        } catch (Exception $e) {
            throw new AnalyticsException("Error saving analytics data", 0, $e);
        }
    }

}
