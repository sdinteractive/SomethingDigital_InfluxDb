<?php
/**
 * Sends a snapshot of current inventory levels for all stock items
 *
 * measurement: inventory
 * tag(s): sku
 * field(s): qty,is_in_stock,backorders,status
 *
 * @todo  Multistore support? - cpei can have multiple values for status that vary by store view
 */
class SomethingDigital_InfluxDb_Model_Measurement_Inventory
    extends SomethingDigital_InfluxDb_Model_Measurement_Abstract
    implements SomethingDigital_InfluxDb_Model_MeasurementInterface
{
    /** @var int */
    protected $statusAttributeId;

    public function __construct()
    {
        parent::__construct();
        $this->statusAttributeId = Mage::getSingleton('eav/config')
            ->getAttribute('catalog_product', 'status')
            ->getAttributeId();
    }

    public function send()
    {
        $collection = Mage::getModel('cataloginventory/stock_item')
            ->getCollection();

        $collection->getSelect()->join(
            array('cpe' => 'catalog_product_entity'),
            'cpe.entity_id = main_table.product_id',
            array('sku' => 'cpe.sku')
        )->join(
            array('cpei' => 'catalog_product_entity_int'),
            'cpe.entity_id = cpei.entity_id',
            array(
                'status' => 'cpei.value',
                'attribute_id' => 'cpei.attribute_id',
                'store_id' => 'cpei.store_id'
            )
        )->where('attribute_id = ?', $this->statusAttributeId
        )->where('store_id = ?', 0);

        $collection->setPageSize(self::MAX_LINES_PER_SEND);
        $currentPage = 1;
        $lastPage = $collection->getLastPageNumber();

        do {
            $data = $collection->setCurPage($currentPage);
            $collection->load();
            $this->api->write($this->data($collection));
            $collection->clear();
            $currentPage++;
        } while ($currentPage <= $lastPage);
    }

    protected function data($collection)
    {
        $data = array();
        foreach ($collection as $item) {
            $data[] = 'inventory,sku=' . $item->getSku() .
                ' qty=' . $item->getQty() .
                ',is_in_stock=' . $item->getIsInStock() .
                ',backorders=' . $item->getBackorders() .
                ',status=' . $item->getStatus();
        }

        return implode(PHP_EOL, $data);
    }
}
