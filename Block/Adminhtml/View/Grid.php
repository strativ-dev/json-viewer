<?php

namespace Strativ\JsonViewer\Block\Adminhtml\View;

use Exception;
use Magento\Backend\Block\Widget\Grid\Extended;
use Magento\Backend\Block\Template\Context;
use Magento\Backend\Helper\Data;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Data\Collection;
use Magento\Framework\Data\Collection\EntityFactoryInterface;
use Magento\Framework\DataObject;
use Magento\Framework\Exception\FileSystemException;
use Magento\Store\Model\ScopeInterface;
use Psr\Log\LoggerInterface;
use Strativ\JsonViewer\Block\Adminhtml\View\Renderer\JsonText;

class Grid extends Extended
{
    /** @var ScopeConfigInterface */
    protected ScopeConfigInterface $scopeConfig;
    /** @var ResourceConnection */
    protected ResourceConnection $resourceConnection;
    /** @var EntityFactoryInterface */
    protected EntityFactoryInterface $entityFactory;
    /** @var LoggerInterface */
    protected LoggerInterface $logger;

    /**
     * @param Context $context
     * @param Data $backendHelper
     * @param ScopeConfigInterface $scopeConfig
     * @param ResourceConnection $resourceConnection
     * @param EntityFactoryInterface $entityFactory
     * @param LoggerInterface $logger
     * @param array $data
     */
    public function __construct(
        Context                $context,
        Data                   $backendHelper,
        ScopeConfigInterface   $scopeConfig,
        ResourceConnection     $resourceConnection,
        EntityFactoryInterface $entityFactory,
        LoggerInterface        $logger,
        array                  $data = []
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->resourceConnection = $resourceConnection;
        $this->entityFactory = $entityFactory;
        $this->logger = $logger;
        parent::__construct($context, $backendHelper, $data);
    }

    /**
     * Initialize grid properties
     *
     * @throws FileSystemException
     */
    protected function _construct(): void
    {
        parent::_construct();
        $this->setId('jsonviewerViewGrid');
        $this->setDefaultSort('row_id');
        $this->setDefaultDir('ASC');
        $this->setUseAjax(false);
        $this->setSaveParametersInSession(false);
        $this->setFilterVisibility(false);
        $this->setPagerVisibility(true);
    }

    /**
     * Retrieve table configuration based on request parameter 'id'
     *
     * @return array|null
     */
    protected function getTableConfig(): ?array
    {
        $id = $this->getRequest()->getParam('id');
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
                return $data[$id];
            }
        }

        return null;
    }

    /**
     * Prepare data collection based on the table configuration
     */
    protected function _prepareCollection(): Extended
    {
        $config = $this->getTableConfig();
        $collection = $this->createEmptyCollection();

        if ($this->isValidConfig($config)) {
            $this->loadCollectionData($collection, $config['table_name'], $config['columns']);
        }

        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    /**
     * Create an empty collection
     *
     * @return Collection
     */
    protected function createEmptyCollection(): Collection
    {
        return new Collection($this->entityFactory);
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
     * Load data into collection from database table
     *
     * @param Collection $collection
     * @param string $tableName
     * @param array $columns
     * @return void
     */
    protected function loadCollectionData(
        Collection $collection,
        string     $tableName,
        array      $columns
    ): void {
        try {
            $connection = $this->resourceConnection->getConnection();
            $select = $connection->select()->from($tableName, $columns);
            $rows = $connection->fetchAll($select);

            $this->addRowsToCollection($collection, $rows);
        } catch (Exception $e) {
            $this->logger->error(
                'Error loading table data in JsonViewer Grid: ' . $e->getMessage(),
                ['exception' => $e]
            );
        }
    }

    /**
     * Add rows to collection with row_id
     *
     * @param Collection $collection
     * @param array $rows
     * @return void
     * @throws Exception
     */
    protected function addRowsToCollection(
        Collection $collection,
        array      $rows
    ): void {
        $index = 1;
        foreach ($rows as $row) {
            if ($this->hasStringValue($row)) {
                $row['row_id'] = $index++;
                $collection->addItem(new DataObject($row));
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
     * Prepare grid columns based on the table configuration
     *
     * @return Extended
     * @throws Exception
     */
    protected function _prepareColumns(): Extended
    {
        $config = $this->getTableConfig();

        if (!$config || !isset($config['columns'])) {
            return parent::_prepareColumns();
        }

        $columns = $config['columns'];

        if (empty($columns) || !is_array($columns)) {
            return parent::_prepareColumns();
        }

        $this->addColumn('row_id', [
            'header' => __('#'),
            'index' => 'row_id',
            'type' => 'number',
            'width' => '50px',
            'filter' => false,
            'sortable' => false
        ]);

        foreach ($columns as $columnName) {
            $this->addColumn($columnName, [
                'header' => __(ucwords(str_replace('_', ' ', $columnName))),
                'index' => $columnName,
                'type' => 'text',
                'renderer' => JsonText::class,
                'filter' => false,
                'sortable' => false
            ]);
        }

        return parent::_prepareColumns();
    }
}
