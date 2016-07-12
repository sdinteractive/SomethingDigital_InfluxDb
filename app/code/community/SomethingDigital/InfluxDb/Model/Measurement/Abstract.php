<?php

abstract class SomethingDigital_InfluxDb_Model_Measurement_Abstract
{
    const MAX_LINES_PER_SEND = 1000;

    /** @var SomethingDigital_InfluxDb_Model_Api */
    protected $api;

    public function __construct()
    {
        $this->api = Mage::getModel('sd_influxdb/api');
    }
}
