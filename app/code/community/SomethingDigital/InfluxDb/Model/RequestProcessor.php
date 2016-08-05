<?php

class SomethingDigital_InfluxDb_Model_RequestProcessor extends Enterprise_PageCache_Model_Processor
{
    const XML_PATH_SHOULD_TRACK = 'sd_influxdb/route_response_headers/enabled';

    const XML_PATH_METADATA_SOURCE = 'sd_influxdb/route_response_headers/metadata_source';

    const RESPONSE_HEADER_PARAMETER = 'Sd-Influxdb-Route';

    public function extractContent($content)
    {
        if (!$content) {
            // Bail, this is a miss.
            return $content;
        }

        $this->addRouteResponseHeader('hit');

        return $content;
    }

    public function addRouteResponseHeader($type = null)
    {
        if (!Mage::getConfig()->getNode(self::XML_PATH_SHOULD_TRACK)) {
            return;
        }

        if ($type === 'hit') {
            $value = $this->routeForHits();
        } else {
            $value = $this->routeForMisses();
        }

        Mage::app()->getResponse()->setHeader(self::RESPONSE_HEADER_PARAMETER, $value);
    }

    protected function routeForHits()
    {
        $configured = (string)Mage::getConfig()->getNode(self::XML_PATH_METADATA_SOURCE);

        if (class_exists($configured)) {
            $source = new $configured;
        } else {
            $source = $this;
        }

        return $source->getMetadata('routing_requested_route') . '/' .
            $source->getMetadata('routing_requested_controller') . '/' .
            $source->getMetadata('routing_requested_action');
    }

    protected function routeForMisses()
    {
        return Mage::app()->getRequest()->getRequestedRouteName() . '/' .
            Mage::app()->getRequest()->getRequestedControllerName() . '/' .
            Mage::app()->getRequest()->getRequestedActionName();
    }
}
