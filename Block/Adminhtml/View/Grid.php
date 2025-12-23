<?php
namespace Strativ\JsonViewer\Block\Adminhtml\View;

use Magento\Backend\Block\Widget\Grid\Extended;
use Magento\Backend\Block\Template\Context;
use Magento\Backend\Helper\Data;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Data\Collection\EntityFactoryInterface;

class Grid extends Extended
{
    protected $scopeConfig;
    protected $resourceConnection;
    protected $entityFactory;

    public function __construct(
        Context $context,
        Data $backendHelper,
        ScopeConfigInterface $scopeConfig,
        ResourceConnection $resourceConnection,
        EntityFactoryInterface $entityFactory,
        array $data = []
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->resourceConnection = $resourceConnection;
        $this->entityFactory = $entityFactory;
        parent::__construct($context, $backendHelper, $data);
    }

    protected function _construct()
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

    protected function getTableConfig()
    {
        $id = $this->getRequest()->getParam('id');
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
                return $data[$id];
            }
        }

        return null;
    }

    protected function _prepareCollection()
    {
        $config = $this->getTableConfig();
        
        if (!$config || !isset($config['table_name']) || !isset($config['columns'])) {
            $collection = new \Magento\Framework\Data\Collection($this->entityFactory);
            $this->setCollection($collection);
            return parent::_prepareCollection();
        }

        $tableName = $config['table_name'];
        $columns = $config['columns'];

        if (empty($columns) || !is_array($columns)) {
            $collection = new \Magento\Framework\Data\Collection($this->entityFactory);
            $this->setCollection($collection);
            return parent::_prepareCollection();
        }

        $collection = new \Magento\Framework\Data\Collection($this->entityFactory);

        try {
            $connection = $this->resourceConnection->getConnection();
            
            $select = $connection->select()
                ->from($tableName, $columns);

            $rows = $connection->fetchAll($select);

            $index = 1;
            foreach ($rows as $row) {
                $hasTextOrJson = false;
                
                foreach ($row as $value) {
                    if (!empty($value) && is_string($value)) {
                        $hasTextOrJson = true;
                        break;
                    }
                }

                if ($hasTextOrJson) {
                    $row['row_id'] = $index++;
                    $collection->addItem(new \Magento\Framework\DataObject($row));
                }
            }
        } catch (\Exception $e) {
            // Handle error silently
        }

        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
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
                'renderer' => \Strativ\JsonViewer\Block\Adminhtml\View\Renderer\JsonText::class,
                'filter' => false,
                'sortable' => false
            ]);
        }

        return parent::_prepareColumns();
    }
}
