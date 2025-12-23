<?php
namespace Strativ\JsonViewer\Model\TableData;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Data\Collection;
use Magento\Framework\DataObject;
use Magento\Framework\Data\Collection\EntityFactoryInterface;

class DataProvider extends \Magento\Ui\DataProvider\AbstractDataProvider
{
    protected $scopeConfig;
    protected $entityFactory;
    protected $request;
    protected $resourceConnection;
    protected $tableConfig;

    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        EntityFactoryInterface $entityFactory,
        ScopeConfigInterface $scopeConfig,
        RequestInterface $request,
        ResourceConnection $resourceConnection,
        array $meta = [],
        array $data = []
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->entityFactory = $entityFactory;
        $this->request = $request;
        $this->resourceConnection = $resourceConnection;
        $this->collection = new Collection($entityFactory);
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
        $this->prepareData();
    }

    protected function getTableConfig()
    {
        if ($this->tableConfig !== null) {
            return $this->tableConfig;
        }

        $id = $this->request->getParam('id');
        if (!$id) {
            return null;
        }

        $savedData = $this->scopeConfig->getValue(
            'strativ_jsonviewer/general/table_columns',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );

        if ($savedData) {
            $data = json_decode($savedData, true);
            if (is_array($data) && isset($data[$id])) {
                $this->tableConfig = $data[$id];
                return $this->tableConfig;
            }
        }

        return null;
    }

    protected function prepareData()
    {
        $config = $this->getTableConfig();
        
        if (!$config || !isset($config['table_name']) || !isset($config['columns'])) {
            return;
        }

        $tableName = $config['table_name'];
        $columns = $config['columns'];

        if (empty($columns) || !is_array($columns)) {
            return;
        }

        try {
            $connection = $this->resourceConnection->getConnection();
            
            $select = $connection->select()
                ->from($tableName, $columns);

            $rows = $connection->fetchAll($select);

            foreach ($rows as $row) {
                $hasTextOrJson = false;
                
                foreach ($row as $value) {
                    if (!empty($value) && is_string($value)) {
                        $hasTextOrJson = true;
                        break;
                    }
                }

                if ($hasTextOrJson) {
                    $this->collection->addItem(new DataObject($row));
                }
            }
        } catch (\Exception $e) {
            return;
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

    public function getMeta()
    {
        $meta = parent::getMeta();
        $config = $this->getTableConfig();
        
        if (!$config || !isset($config['columns'])) {
            return $meta;
        }

        $columns = $config['columns'];
        
        foreach ($columns as $columnName) {
            $meta['jsonviewer_view_columns']['children'][$columnName] = [
                'arguments' => [
                    'data' => [
                        'config' => [
                            'label' => ucwords(str_replace('_', ' ', $columnName)),
                            'dataType' => 'text',
                            'formElement' => 'input',
                            'visible' => true,
                            'sortOrder' => 10,
                        ]
                    ]
                ]
            ];
        }

        return $meta;
    }
}
