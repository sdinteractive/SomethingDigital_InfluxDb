# SomethingDigital_InfluxDb

A set of tools that are useful when connecting Magento to InfluxDb

### Features

- "InfluxDB" section under the "Advanced" tab for configuring connection
- `SomethingDigital_InfluxDb_Model_Api` class for communicating with InfluxDB
- `SomethingDigital_InfluxDb_Model_MeasurementInterface` interface for creating measurements
- Measurements in `app/code/community/SomethingDigital/InfluxDb/Model/Measurement`
- `SomethingDigital_Shell_Influxdb` script for executing measurement groups
- Send a customer response header (`Sd-Influxdb-Route`) with routing information

### Sending Measurements

The Magento cron has a bad habit of getting stuck. **In fact, it's one of the most important things that you should be monitoring!** As such we don't rely on it delivery of measurements to InfluxDb. Instead, we add additional crontab entries to execute specific measurement groups via `shell/sd_influxdb.php`. For example...

**`crontab`**

```
* * * * * php /var/www/html/shell/sd_influxdb.php --group one_minute
```

**`local.xml`**

```xml
<config>
    <sd_influxdb>
        <groups>
            <one_minute>
                <inventory>sd_influxdb/measurement_inventory</inventory>
            </one_minute>
        </groups>
    </sd_influxdb>
</config>
```

The `send` method will be called on each model specified under the measurement group. In `send` you can get the data you need and send it to InfluxDB using `SomethingDigital_InfluxDb_Model_Api`. See the measurements in `app/code/community/SomethingDigital/InfluxDb/Model/Measurement` for reference.

You can add additional groups and add additional measurements to groups as needed.

### Sending Routing info in custom response header

Routing information for a given request can be sent in a response header. A common use case for this would be to see response codes counts on a route-by-route basis. A new Apache CustomLog format can be used to save the response headers to the log files.

#### Turning on the custom header

**`local.xml`**

```xml
<config>
    <sd_influxdb>
        <route_response_headers>
            <enabled>1</enabled>
        </route_response_headers>
    </sd_influxdb>
</config>
```

In order to add a response headers to full page cache hits `SomethingDigital_InfluxDb_Model_RequestProcessor` also needs to be registered as a request processor and **must go last**.

**`enterprise.xml`**

```xml
<config>
    <global>
        <cache>
            <request_processors>
                <ee>Enterprise_PageCache_Model_Processor</ee>
                <zz_sd_influxdb>SomethingDigital_InfluxDb_Model_RequestProcessor</zz_sd_influxdb>
            </request_processors>
        </cache>
    </global>
</config>
```

#### Compatibility with custom request processors

If, for some reason, your site is not using `Enterprise_PageCache_Model_Processor` as the processor for FPC, you'll need to declare that processor as the `metadata_source`.

**`local.xml`**

```xml
<config>
    <sd_influxdb>
        <route_response_headers>
            <enabled>1</enabled>
            <metadata_source>Elastera_EnterprisePageCacheSSL_Model_Processor</metadata_source>
        </route_response_headers>
    </sd_influxdb>
</config>
```

