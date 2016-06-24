<?php

require_once 'abstract.php';

// @todo Use a shell script to run this to to make sure jobs don't overlap
class SomethingDigital_Shell_Influxdb extends Mage_Shell_Abstract
{
    public function run()
    {
        $group = $this->getArg('group');
        if (!$group) {
            echo 'Group is required.' . PHP_EOL;
            return;
        }

        $tasks = Mage::getConfig()->getNode('sd_influxdb/groups/' . $group);
        if (!$tasks) {
            echo 'No tasks.' . PHP_EOL;
            return;
        }

        foreach ($tasks->asArray() as $task) {
            $model = Mage::getModel($task);
            if (!$model instanceof SomethingDigital_InfluxDb_Model_MeasurementInterface) {
                $message = $task . ' does not implement the required interface';
                echo $message . PHP_EOL;
                Mage::logException(new Exception($message));
                continue;
            }

            $model->send();
        }
    }
}

$shell = new SomethingDigital_Shell_Influxdb();
$shell->run();
