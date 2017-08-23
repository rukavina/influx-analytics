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

/**
 * Class AnalyticsTrait
 */
trait AnalyticsTrait {

    /**
     * Get timezone offset in hours
     * 
     * @param  string $origin_tz
     * @param  string $format 
     * @return int
     */
    public function getTimezoneHourOffset($origin_tz = 'UTC', $format = 'h') {
        $remote_tz = 'UTC';
        if ($origin_tz === 'UTC') {
            return 0 . 'h';
        }
        $origin_dtz = new \DateTimeZone($origin_tz);
        $remote_dtz = new \DateTimeZone($remote_tz);
        $origin_dt = new \DateTime("now", $origin_dtz);
        $remote_dt = new \DateTime("now", $remote_dtz);

        $offset = $origin_dtz->getOffset($origin_dt) - $remote_dtz->getOffset($remote_dt);
        return $offset / 3600 . $format;
    }

    /**
     * Normalize UTC 
     * 
     * @param  string $date 
     * @return string
     */
    public function normalizeUTC($date) {
        $parts = explode(" ", $date);
        if (!is_array($parts) || count($parts) != 2) {
            throw new AnalyticsNormalizeException("Error normalize date, wrong format[$date]");
        }
        return $parts[0] . "T" . $parts[1] . "Z";
    }

    /**
     * Find key by sub value
     * 
     * @param  string $needle   
     * @param  array $haystack 
     * @return string          
     */
    public function arrayMultiSearch($needle, $haystack) {
        foreach ($haystack as $key => $data) {
            if (in_array($needle, $data)) {
                return $key;
            }
        }
        return false;
    }

}
