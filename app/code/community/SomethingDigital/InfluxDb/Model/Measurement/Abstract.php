<?php

abstract class SomethingDigital_InfluxDb_Model_Measurement_Abstract
{
    /** @var SomethingDigital_InfluxDb_Model_Api */
    protected $api;

    public function __construct()
    {
        $this->api = Mage::getModel('sd_influxdb/api');
    }
}
