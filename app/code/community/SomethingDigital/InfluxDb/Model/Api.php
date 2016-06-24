<?php

class SomethingDigital_InfluxDb_Model_Api
{
    const XML_PREFIX = 'sd_influxdb/general/';

    public function __construct()
    {
        $this->client = new Varien_Http_Client();
    }

    public function write($data)
    {
        $client = $this->client;
        $client->setUri($this->uri('write'));
        $client->setMethod(Zend_Http_Client::POST);
        $client->setRawData($data);
        $response = $client->request();

        if ($response->getStatus() === 204) {
            return true;
        } else {
            // @todo How do we actually want to handle this?
            return false;
        }
    }

    protected function uri($operation)
    {
        return $this->config('uri') . '/' . $operation . '?db=' . $this->config('db');
    }

    protected function config($path)
    {
        return Mage::getStoreConfig(self::XML_PREFIX . $path);
    }
}
