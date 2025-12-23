<?php

namespace Strativ\JsonViewer\Block\Adminhtml\Form\Field;

use Magento\Framework\View\Element\Context;
use Strativ\JsonViewer\Model\Config\Source\TableColumns as TableColumnsSource;
use Magento\Framework\View\Element\Html\Select;

class ColumnMultiselect extends Select
{
    /** @var TableColumnsSource */
    protected TableColumnsSource $tableColumnsSource;
    /** @var string */
    private string $tableName = '';

    /**
     * Constructor
     *
     * @param Context $context
     * @param TableColumnsSource $tableColumnsSource
     * @param array $data
     */
    public function __construct(
        Context            $context,
        TableColumnsSource $tableColumnsSource,
        array              $data = []
    )
    {
        parent::__construct($context, $data);
        $this->tableColumnsSource = $tableColumnsSource;
    }

    /**
     * Set the input name for the multiselect
     *
     * @param string $value
     * @return $this
     */
    public function setInputName(string $value): self
    {
        return $this->setName($value . '[]');
    }

    /**
     * Set the table name for which to fetch columns
     *
     * @param string $tableName
     * @return $this
     */
    public function setTableName($tableName): self
    {
        $this->tableName = $tableName;
        return $this;
    }

    /**
     * Get the current table name
     *
     * @return string
     */
    public function getTableName(): string
    {
        return $this->tableName;
    }

    /**
     * Render the multiselect HTML
     *
     * @return string
     */
    protected function _toHtml(): string
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

    /**
     * Calculate a unique hash for an option value
     *
     * @param string $optionValue
     * @return string
     */
    public function calcOptionHash($optionValue): string
    {
        return sprintf('%u', crc32($this->getName() . $this->getId() . $optionValue));
    }
}
