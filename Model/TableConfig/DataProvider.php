<?php
namespace Strativ\JsonViewer\Model\TableConfig;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Data\Collection;
use Magento\Framework\DataObject;
use Magento\Framework\Data\Collection\EntityFactoryInterface;

class DataProvider extends \Magento\Ui\DataProvider\AbstractDataProvider
{
    protected $scopeConfig;
    protected $entityFactory;

    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        ScopeConfigInterface $scopeConfig,
        EntityFactoryInterface $entityFactory,
        array $meta = [],
        array $data = []
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->entityFactory = $entityFactory;
        $this->collection = new Collection($entityFactory);
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
        $this->prepareData();
    }

    protected function prepareData()
    {
        $savedData = $this->scopeConfig->getValue(
            'strativ_jsonviewer/general/table_columns',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );

        if ($savedData) {
            $data = json_decode($savedData, true);
            if (is_array($data)) {
                $index = 1;
                foreach ($data as $rowId => $item) {
                    $columns = isset($item['columns']) && is_array($item['columns']) 
                        ? implode(', ', $item['columns']) 
                        : '';
                    
                    $this->collection->addItem(new DataObject([
                        'id' => $rowId,
                        'index' => $index++,
                        'table' => $item['table_name'] ?? '',
                        'columns' => $columns
                    ]));
                }
            }
        }
    }

    public function getData()
    {
        $items = [];
        foreach ($this->getCollection() as $item) {
            $items[] = $item->getData();
        }

        return [
            'totalRecords' => $this->getCollection()->getSize(),
            'items' => $items
        ];
    }
}