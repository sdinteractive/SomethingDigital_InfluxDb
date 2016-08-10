<?php

class SomethingDigital_InfluxDb_Model_Api
{
    public function __construct($args = array())
    {
        $this->client = new Varien_Http_Client();
        $this->config = Mage::getModel('sd_influxdb/config');
        $this->setupAuth();
        $this->precision = ($args['precision']) ? $args['precision'] : 's';
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
            Mage::logException(new Exception('InfluxDB write failed, status=' . $response->getStatus()));
            return false;
        }
    }

    protected function setupAuth()
    {
        $username = $this->config->get('general/username');
        $password = $this->config->get('general/password');
        if ($username && $password) {
            $this->client->setAuth($username, $password);
        }
    }

    protected function uri($operation)
    {
        return $this->config->get('general/uri') . '/' . $operation .
            '?db=' . $this->config->get('general/db') .
            '&precision=' . $this->precision;
    }
}
