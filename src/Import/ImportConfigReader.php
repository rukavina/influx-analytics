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

namespace Vorbind\InfluxAnalytics\Import;

use Vorbind\InfluxAnalytics\Import\ImportConfigReaderInterface;
use \Exception;

/**
 * Provides an API for analytics import
 */
class ImportConfigReader implements ImportConfigReaderInterface {
    
    /**
     *
     * @var array
     */
    protected $metrics;

    /**
     *
     * @var array 
     */
    protected $dbs;
    
    public function __construct(string $config) {
        try {
            if (null == $config) {
                throw new Exception("File import.config.json is missing.");
            }
            $configJson = file_get_contents($config);
            $configArray = json_decode($configJson, true);

            if (!is_array($configArray) || count($configArray) == 0) {
                throw new Exception("Configuration file import.config.json is not valid!");
            }
            
            if (!isset($configArray['metrics'])) {
                throw new Exception("Metrics are not configured!");
            }
        
            if (!$configArray['dbs']) {
                throw new Exception("Dbs are not configured!");
            }

            $this->metrics = $configArray['metrics'];
            $this->dbs = $configArray['dbs'];
            
        } catch (Exception $e) {
            throw new Exception("Faild reading import.config.json file.");
        }
    }

    /**
     * Get metrics config
     */
    public function getMetricsConfig() {
        return $this->metrics;
    }

    /**
     * Get dbs config
     */
    public function getDbsConfig() {
        return $this->dbs;
    }

}
