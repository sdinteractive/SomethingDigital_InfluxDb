<?php
/**
 * Sends a snapshot of current inventory levels for all stock items
 *
 * measurement: inventory
 * tag key(s): sku
 * tag values(s): qty
 */
class SomethingDigital_InfluxDb_Model_Measurement_Inventory
    implements SomethingDigital_InfluxDb_Model_MeasurementInterface
{
    const CHUNK_SIZE = 1000;

    public function send()
    {
        $collection = Mage::getModel('cataloginventory/stock_item')
            ->getCollection();

        $api = Mage::getModel('sd_influxdb/api');

        $collection->getSelect()->join(
            array('cpe' => 'catalog_product_entity'),
            'cpe.entity_id = main_table.product_id',
            array('sku' => 'cpe.sku')
        );

        $collection->setPageSize(self::CHUNK_SIZE);
        $currentPage = 1;
        $lastPage = $collection->getLastPageNumber();

        do {
            $data = $collection->setCurPage($currentPage);
            $collection->load();
            $api->write($this->data($collection));
            $collection->clear();
            $currentPage++;
        } while ($currentPage <= $lastPage);
    }

    protected function data($collection)
    {
        $data = '';
        foreach ($collection as $item) {
            if ($data !== '') {
                $data .= PHP_EOL;
            }

            $data .= 'inventory,sku=' . $item->getSku() . ' qty=' . $item->getQty();
        }

        return $data;
    }
}
