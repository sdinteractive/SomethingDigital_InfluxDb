<?php
/**
 * Sends data about FPC hit rate.
 *
 * measurement(s): request_response|container_miss
 * tag(s): type, route, hostname, customerGroup, ip, url, container
 * field(s): count
 *
 * This is meant to be used in tandem with
 * Mpchadwick_PageCacheHitRate_Model_Tracker_Redis.
 *
 * You need to provide the alias to the Redis tracker in the config under
 * sd_influxdb/fpc_hit_rate/tracker_alias.
 *
 * ---------------------------
 * Important Note!
 * ---------------------------
 * In Mpchadwick_PageCacheHitRate tags can (and should) be stripped on
 * a per tracker basis via the `strip` node to reduce cardinality.
 *
 * It is *highly* recommended that you strip at least ip and url for
 * Mpchadwick_PageCacheHitRate_Model_Tracker_Redis as they are highly cardinal
 * and will rapidly max out memory usage.
 *
 * Refer the the documentation of
 * Mpchadwick_PageCacheHitRate for details on how to strip these parameters.
 *
 */
class SomethingDigital_InfluxDb_Model_Measurement_FpcHitRate
    extends SomethingDigital_InfluxDb_Model_Measurement_Abstract
    implements SomethingDigital_InfluxDb_Model_MeasurementInterface
{
    const MAX_LINES_PER_SEND = 1000;

    public function send()
    {
        $tracker = Mage::getModel('mpchadwick_pagecachehitrate/tracker_redis');
        $connection = $tracker->connection($this->alias());
        $raw = array_merge(
            $connection->hGetAll(Mpchadwick_PageCacheHitRate_Model_Tracker_Redis::KEY_PREFIX . 'RequestResponse'),
            $connection->hGetAll(Mpchadwick_PageCacheHitRate_Model_Tracker_Redis::KEY_PREFIX . 'ContainerMiss')
        );
        $data = array();
        foreach ($raw as $key => $val) {
            $data[] = $this->line($key, $val);
            if (count($data) >= self::MAX_LINES_PER_SEND) {
                $this->api->write(implode(PHP_EOL, $data));
                $data = array();
            }
        }

        if ($data) {
            $this->api->write(implode(PHP_EOL, $data));
        }
    }

    protected function alias()
    {
        return (string)Mage::getConfig()->getNode('sd_influxdb/fpc_hit_rate/tracker_alias');
    }

    protected function line($key, $val)
    {
        $measurement = 'request_response';
        $tags = explode('&', $key);
        foreach ($tags as $key => &$tag) {
            // If the tag is empty (can happen with e.g. customer group) skip it.
            // Empty tags are no bueno with InfluxDB.
            if (substr($tag, -1) === '=') {
                unset($tags[$key]);
                continue;
            }

            if (stripos($tag, 'container=') !== false) {
                $measurement = 'container_miss';
            }

            // Make it human read able and replace any characters that need to be
            // escaped with an underscore...
            // See https://docs.influxdata.com/influxdb/v0.13/write_protocols/write_syntax/#escaping-characters
            $array = explode('=', $tag);
            $array[1] = str_replace(array(' ', ',', '='), '_', urldecode($array[1]));
            $tag = implode('=', $array);
        }

        $tags = implode(',', $tags);
        return $measurement . ',' . $tags . ' count=' . $val;
    }
}
