<?php
/**
 * Sends data about execution of Magento cron
 *
 * measurement: cron
 * tag(s): job_code,mode
 * field(s): time_since_last_run, average_runtime
 *
 * Here's an example (TICKScript) of how to alert on this...
 *
 * ```
 * batch
 *  |query('SELECT min(time_since_last_run) FROM "mydb"."default"."cron"')
 *    .groupBy('mode')
 *    .period(5m)
 *    .every(5m)
 *  |alert()
 *    .crit(lambda: "min" > 14400)
 *    .stateChangesOnly()
 *    .log('/tmp/cron_batch.log')
 * ```
 *
 * This will get the minimum time_since_last_run (in seconds) every 5 minutes
 * grouped by mode and alert if greater than 4 hours
 *
 */
class SomethingDigital_InfluxDb_Model_Measurement_Cron
    extends SomethingDigital_InfluxDb_Model_Measurement_Abstract
    implements SomethingDigital_InfluxDb_Model_MeasurementInterface
{
    protected $modeMap;

    public function send()
    {
        $jobs = Mage::getModel('cron/schedule')->getCollection();
        $jobs->getSelect()
            ->reset(Zend_Db_Select::COLUMNS)
            ->columns(array(
                'job_code',
                'TIME_TO_SEC(TIMEDIFF(UTC_TIMESTAMP(), MAX(finished_at))) AS time_since_last_run',
                'AVG(UNIX_TIMESTAMP(finished_at) - UNIX_TIMESTAMP(executed_at)) AS average_runtime')
            )
            ->where('finished_at IS NOT NULL')
            ->group('job_code');

        $data = array();
        foreach ($jobs as $job) {
            $data[] = $this->line($job);
        }

        $this->api->write(implode(PHP_EOL, $data));
    }

    protected function line($job)
    {
        return 'cron,job_code=' . $job['job_code'] . ',mode=' . $this->mode($job['job_code']) .
            ' time_since_last_run=' . $job['time_since_last_run'] . ',average_runtime=' . $job['average_runtime'];
    }

    protected function mode($jobCode)
    {
        if (is_null($this->modeMap)) {
            $this->generateModeMap();
        }

        return $this->modeMap[$jobCode];
    }

    protected function generateModeMap()
    {
        $this->modeMap = array();

        // @see Mage_Cron_Model_Observer::_generateJobs()
        $jobs = array_merge(
            Mage::getConfig()->getNode('crontab/jobs')->asArray(),
            Mage::getConfig()->getNode('default/crontab/jobs')->asArray()
        );

        foreach ($jobs as $jobCode => $jobConfig) {
            if ($jobConfig['schedule']['config_path']) {
                $cronExpr = Mage::getStoreConfig($jobConfig['schedule']['config_path']);
            } else if ($jobConfig['schedule']['cron_expr']) {
                $cronExpr = $jobConfig['schedule']['cron_expr'];
            }

            if ($cronExpr == 'always') {
                $mode = 'always';
            } else if ($cronExpr) {
                $mode = 'default';
            } else {
                $mode = 'unknown';
            }

            $this->modeMap[$jobCode] = $mode;
        }
    }
}
