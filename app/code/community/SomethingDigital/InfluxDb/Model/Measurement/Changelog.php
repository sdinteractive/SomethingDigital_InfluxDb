<?php
/**
 * Sends data about status of Enterprise_Index changelogs
 *
 * measurement: changelog
 * tag(s): name
 * field(s): metadata_version, version
 *
 */
class SomethingDigital_InfluxDb_Model_Measurement_Changelog
    extends SomethingDigital_InfluxDb_Model_Measurement_Abstract
    implements SomethingDigital_InfluxDb_Model_MeasurementInterface
{
    public function send()
    {
        $metadatas = Mage::getModel('enterprise_mview/metadata')->getCollection();
        $data = array();
        foreach ($metadatas as $metadata) {
            $data[] = $this->line($metadata);
        }

        $this->api->write(implode(PHP_EOL, $data));
    }

    protected function line($metadata)
    {
        $changelog = Mage::getModel('enterprise_index/changelog', array(
            'connection' => $this->readCon,
            'metadata' => $metadata,
        ));

        return 'changelog,name=' . $metadata->getChangelogName() .
            ' metadata_version=' . $metadata->getVersionId() .
            ',version=' . $changelog->getLastVersionId();
    }
}
