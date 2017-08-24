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

use \PDO;
use \Exception;
use Vorbind\InfluxAnalytics\Import\ImportConfigReaderInterface;
 
/**
 * Provides an API for database adapter
 */
class MysqlDatabaseAdapter implements DatabaseAdapterInterface {
    
    /**
     *
     * @var PDO 
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
     * @return PDO
     * @throws Exception
     */
    public function getDatabaseAdapter() {
        try {           
            if (isset($this->db)) {
                return $this->db;
            }
        
            $config = $this->reader->getDbsConfig();
            if (!isset($config['mysql']) 
                    || !isset($config['mysql']["username"]) 
                    || !isset($config['mysql']["password"]) 
                    || !isset($config['mysql']["host"]) 
                    || !isset($config['mysql']["database"]) ) {
                throw new Exception("Mysql config is not valid!");
            }
            
            $username = $config['mysql']["username"];
            $password = $config['mysql']["password"];
            $host = $config['mysql']["host"];
            $dbname = $config['mysql']["database"];
            
            $this->db = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
            $this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            return $this->db;
        } catch (Exception $ex) {
            print("ERROR:>>>>>>>" . $ex->getMessage());
            throw new Exception("Faild to connect mysql db!");
        }
    } 
    	
} 
