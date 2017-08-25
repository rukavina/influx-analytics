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
 * Provides an API for analytics
 */
interface AnalyticsInterface {

    /**
     * Get analytics data in right time zone by period and granularity 
     *  
     * @param string $rp
     * @param string $metric
     * @param array $tags
     * @param string $granularity
     * @param string $startDt
     * @param string $endDt
     * @param string $timeoffset
     * @return int
     * @throws AnalyticsException
     */
    public function getData($rp, $metric, $tags, $granularity, $startDt = null, $endDt = null, $timeoffset = '0h');

    
    /**
     * Returns analytics total for right metric
     * 
     * @param string $rp
     * @param string $metric
     * @param array $tags
     * @param string $startDt
     * @param string $endDt
     * @return int
     * @throws AnalyticsException
     */
    public function getTotal($rp, $metric, $tags, $startDt = null, $endDt = null);
    
    /**
     * Save analytics
     * 
     * @param string $metric
     * @param array $tags
     * @param int $value
     * @param string $date
     * @param string $rp
     * @throws AnalyticsException
     */   
    public function save($metric, $tags, $value, $utc, $rp);
}