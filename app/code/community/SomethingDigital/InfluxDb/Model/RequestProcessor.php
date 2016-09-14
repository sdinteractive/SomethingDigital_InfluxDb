<?php
/**
 * Adds an Sd-Influxdb-Route response header
 *
 * E.g.
 * Sd-Influxdb-Route: catalog/category/view
 *
 * The intention is to record this in the Apache log so that response codes
 * can be analyzed on a per route basis.
 *
 * Configuration needs to happen in an xml file in app/etc in order to be
 * compatible with FPC.
 */
class SomethingDigital_InfluxDb_Model_RequestProcessor
{
    const XML_PATH_SHOULD_TRACK = 'sd_influxdb/route_response_headers/enabled';

    const XML_PATH_METADATA_SOURCE = 'sd_influxdb/route_response_headers/metadata_source';

    const DEFAULT_METADATA_SOURCE = 'Enterprise_PageCache_Model_Processor';

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

        if (!$value) {
            return;
        }

        Mage::app()->getResponse()->setHeader(self::RESPONSE_HEADER_PARAMETER, $value);
    }

    protected function routeForHits()
    {
        $class = (string)Mage::getConfig()->getNode(self::XML_PATH_METADATA_SOURCE);

        if (!$class || !class_exists($class)) {
            $class = self::DEFAULT_METADATA_SOURCE;
        }

        $source = new $class;

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
