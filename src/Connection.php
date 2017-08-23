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

use Vorbind\InfluxAnalytics\Exception\AnalyticsException;

/**
 *  Analytics
 *
 *  Use this section to define what this class is doing, the PHPDocumentator will use this
 *  to automatically generate an API documentation using this information.
 *
 *  @author sasa.rajkovic
 */
class Connection {

    private $client;
    private $host;
    private $port;
    private $username;
    private $password;

    public function __construct($username = '', $password = '', $host = 'localhost', $port = '8086') {
        $this->host = $host;
        $this->port = $port;
        $this->username = $username;
        $this->password = $password;
    }

    /**
     * Get database
     * 
     * @param string $name
     * @return InfluxDB\Database $db
     * @throws AnalyticsException
     */
    public function getDatabase($name) {
        try {
            if (!isset($name)) {
                throw InvalidArgumentException::invalidType('"db name" driver option', $name, 'string');
            }

            if (null == $this->client) {
                $this->client = new \InfluxDB\Client($this->host, $this->port, $this->username, $this->password);
            }

            if (!isset($this->dbs[$name])) {
                $this->dbs[$name] = $this->client->selectDB($name);
            }
            
            return $this->dbs[$name];
        } catch (Exception $e) {
            throw new AnalyticsException("Connecting influx db faild", 0, $e);
        }
    }

}
