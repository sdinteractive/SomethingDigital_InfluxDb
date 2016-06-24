# SomethingDigital_InfluxDb

Base module for sending data for InfluxDB.

### Features

- "InfluxDB" section under the "Advanced" tab for configuring connection
- `SomethingDigital_InfluxDb_Model_Api` class for communicating with InfluxDB
- `SomethingDigital_InfluxDb_Model_MeasurementInterface` interface for creating measurements
- `SomethingDigital_Shell_Influxdb` script for executing measurement groups

### Usage

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
                <inventory>sd_influxdbinventory/measurement</inventory>
            </one_minute>
        </groups>
    </sd_influxdb>
</config>
```

The `send` method will be called on each model specified under the measurement group. In `send` you can get the data you need and send it to InfluxDB using `SomethingDigital_InfluxDb_Model_Api`.

You can add additional groups and add additional measurements to groups as needed.
