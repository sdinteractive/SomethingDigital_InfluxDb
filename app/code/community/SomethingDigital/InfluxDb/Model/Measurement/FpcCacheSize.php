<?php
/**
 * Sends the cache size
 *
 * measurement: fpc_cache_size
 * value(s): size
 */
class SomethingDigital_InfluxDb_Model_Measurement_FpcCacheSize
    extends SomethingDigital_InfluxDb_Model_Measurement_Abstract
    implements SomethingDigital_InfluxDb_Model_MeasurementInterface
{
    public function send()
    {
        // @see Enterprise_PageCache_Model_Processor::processRequestResponse
        $instance = Enterprise_PageCache_Model_Cache::getCacheInstance();
        $size = (int) $instance->load(Enterprise_PageCache_Model_Processor::CACHE_SIZE_KEY);
        $this->api->write('fpc_cache_size size=' . $size);
    }
}
