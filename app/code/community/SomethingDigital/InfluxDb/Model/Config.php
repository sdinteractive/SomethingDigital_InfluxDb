<?php

class SomethingDigital_InfluxDb_Model_Config
{
    const XML_PREFIX = 'sd_influxdb/';

    public function get($path)
    {
        return Mage::getStoreConfig(self::XML_PREFIX . $path);
    }
}
