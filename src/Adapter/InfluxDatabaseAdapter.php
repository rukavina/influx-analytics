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

namespace Vorbind\InfluxAnalytics\Adapter;

use InfluxDB\Database as InfluxDB;
use \Exception;
use Vorbind\InfluxAnalytics\Import\ImportConfigReaderInterface;
 
/**
 * Provides an API for database adapter
 */
class InfluxDatabaseAdapter implements DatabaseAdapterInterface {
    
    /**
     *
     * @var InfluxDB 
     */
    protected $db;
   
    /**
     *
     * @var ImportConfigReaderInterface 
     */
    protected $reader;
    
    
    public function __construct(ImportConfigReaderInterface $reader) {
        $this->reader = $reader;
    }
    
    /**
     * Get db adapter
     * 
     * @return InfluxDB
     * @throws Exception
     */
    public function getDatabaseAdapter() {
        try {             
            if (isset($this->db)) {
                return $this->db;
            }
            
            $config = $this->reader->getDbsConfig();
            
            if ( !isset($config['influx']) 
                    || !isset($config['influx']["username"]) 
                    || !isset($config['influx']["password"]) 
                    || !isset($config['influx']["host"]) 
                    || !isset($config['influx']["port"]) 
                    || !isset($config['influx']["database"]) ) {
                throw new Exception("Influx config is not valid!");
            }
            
            $username = $config['influx']["username"];
            $password = $config['influx']["password"];
            $host = $config['influx']["host"];
            $port = $config['influx']["port"];
            $dbname = $config['influx']["database"];
        
            $client = new \InfluxDB\Client($host, $port, $username, $password);
            $this->db = $client->selectDB($dbname);
            return $this->db;
        } catch (Exception $ex) {
            print("ERROR:" . $ex->getMessage());
            throw new Exception("Faild connect to influx db!");
        }
    } 
    	
} 
