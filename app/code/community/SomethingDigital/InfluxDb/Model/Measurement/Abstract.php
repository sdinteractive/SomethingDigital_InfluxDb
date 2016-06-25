<?php

abstract class SomethingDigital_InfluxDb_Model_Measurement_Abstract
{
    public function __construct()
    {
        $this->api = Mage::getModel('sd_influxdb/api');
    }
}
