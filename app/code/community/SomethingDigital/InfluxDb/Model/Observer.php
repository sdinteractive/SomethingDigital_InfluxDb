<?php

class SomethingDigital_InfluxDb_Model_Observer
{
    public function handleControllerFrontSendResponseBefore(Varien_Event_Observer $observer)
    {
        Mage::getModel('sd_influxdb/requestProcessor')->addRouteResponseHeader();
    }
}
