<?php

namespace Strativ\JsonViewer\Model\TableData;

use Exception;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Data\Collection;
use Magento\Framework\DataObject;
use Magento\Framework\Data\Collection\EntityFactoryInterface;
use Magento\Ui\DataProvider\AbstractDataProvider;
use Magento\Store\Model\ScopeInterface;

class DataProvider extends AbstractDataProvider
{
    /** @var ScopeConfigInterface */
    protected ScopeConfigInterface $scopeConfig;
    /** @var EntityFactoryInterface */
    protected EntityFactoryInterface $entityFactory;
    /** @var RequestInterface */
    protected RequestInterface $request;
    /** @var ResourceConnection */
    protected ResourceConnection $resourceConnection;
    /** @var array|null */
    protected ?array $tableConfig;

    /**
     * @param string $name
     * @param string $primaryFieldName
     * @param string $requestFieldName
     * @param EntityFactoryInterface $entityFactory
     * @param ScopeConfigInterface $scopeConfig
     * @param RequestInterface $request
     * @param ResourceConnection $resourceConnection
     * @param array $meta
     * @param array $data
     */
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

    /**
     * Retrieve table configuration based on request parameter 'id'
     *
     * @return array|null
     */
    protected function getTableConfig(): ?array
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
            ScopeInterface::SCOPE_STORE
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

    /**
     * Prepare data collection based on the table configuration
     */
    protected function prepareData(): void
    {
        $config = $this->getTableConfig();

        if (!$this->isValidConfig($config)) {
            return;
        }

        try {
            $this->loadTableData($config['table_name'], $config['columns']);
        } catch (Exception $e) {
            return;
        }
    }

    /**
     * Validate table configuration
     *
     * @param array|null $config
     * @return bool
     */
    protected function isValidConfig(?array $config): bool
    {
        if (!$config || !isset($config['table_name']) || !isset($config['columns'])) {
            return false;
        }

        $columns = $config['columns'];
        return !empty($columns) && is_array($columns);
    }

    /**
     * Load table data into collection
     *
     * @param string $tableName
     * @param array $columns
     * @return void
     * @throws Exception
     */
    protected function loadTableData(string $tableName, array $columns): void
    {
        $connection = $this->resourceConnection->getConnection();

        $select = $connection->select()
            ->from($tableName, $columns);

        $rows = $connection->fetchAll($select);

        foreach ($rows as $row) {
            if ($this->hasStringValue($row)) {
                $this->collection->addItem(new DataObject($row));
            }
        }
    }

    /**
     * Check if row contains any string value
     *
     * @param array $row
     * @return bool
     */
    protected function hasStringValue(array $row): bool
    {
        foreach ($row as $value) {
            if (!empty($value) && is_string($value)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Get data for the UI component
     *
     * @return array
     */
    public function getData(): array
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

    /**
     * Get meta configuration for the UI component
     *
     * @return array
     */
    public function getMeta(): array
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
