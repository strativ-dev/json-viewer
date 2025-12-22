<?php
namespace Strativ\JsonViewer\Block\Adminhtml\Form\Field;

use Magento\Framework\View\Element\Context;
use Strativ\JsonViewer\Model\Config\Source\TableColumns as TableColumnsSource;

class ColumnMultiselect extends \Magento\Framework\View\Element\Html\Select
{
    protected $tableColumnsSource;
    private $tableName;

    public function __construct(
        Context $context,
        TableColumnsSource $tableColumnsSource,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->tableColumnsSource = $tableColumnsSource;
    }

    public function setInputName($value)
    {
        return $this->setName($value . '[]');
    }

    public function setInputId($value)
    {
        return $this->setId($value);
    }

    public function setTableName($tableName)
    {
        $this->tableName = $tableName;
        return $this;
    }

    public function getTableName()
    {
        return $this->tableName;
    }

    protected function _toHtml()
    {
        $allTablesColumns = $this->tableColumnsSource->getAllTablesColumns();
        
        // If we have a table name, render its columns as options
        if ($this->tableName) {
            $tableColumns = $this->tableColumnsSource->getColumnsForTable($this->tableName);
            $this->setOptions($tableColumns);
        } else {
            // No table selected yet, set empty options
            $this->setOptions([]);
        }
        
        $this->setClass('admin__control-multiselect');
        $this->setExtraParams('multiple="multiple" data-all-columns=\'' . json_encode($allTablesColumns) . '\'');
        
        return parent::_toHtml();
    }

    public function calcOptionHash($optionValue)
    {
        return sprintf('%u', crc32($this->getName() . $this->getId() . $optionValue));
    }
}
