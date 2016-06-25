<?php
/**
 * Sends the time since last run for each job was successfully executed
 *
 * measurement: cron
 * tag key(s): job_code,mode
 * tag values(s): time_since_last_run
 *
 * @todo average_run_time tag value
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
                'TIME_TO_SEC(TIMEDIFF(UTC_TIMESTAMP(), MAX(finished_at))) as time_since_last_run')
            )
            ->where('finished_at IS NOT NULL')
            ->group('job_code');

        $data = [];
        foreach ($jobs as $job) {
            $data[] = $this->line($job);
        }

        $this->api->write(implode(PHP_EOL, $data));
    }

    protected function line($job)
    {
        return 'cron,job_code=' . $job['job_code'] . ',mode=' . $this->mode($job['job_code']) .
            ' time_since_last_run=' . $job['time_since_last_run'];
    }

    protected function mode($job_code)
    {
        if (is_null($this->modeMap)) {
            $this->modeMap = array();
            $jobs = array_merge(
                Mage::getConfig()->getNode('crontab/jobs')->asArray(),
                Mage::getConfig()->getNode('default/crontab/jobs')->asArray()
            );

            foreach ($jobs as $jobCode => $jobConfig) {
                // @see Mage_Cron_Model_Observer::__generateJobs()
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

        return $this->modeMap[$job_code];
    }
}
