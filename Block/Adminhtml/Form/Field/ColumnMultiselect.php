<?php
namespace Strativ\JsonViewer\Block\Adminhtml\Form\Field;

use Magento\Framework\View\Element\Context;
use Strativ\JsonViewer\Model\Config\Source\TableColumns as TableColumnsSource;

class ColumnMultiselect extends \Magento\Framework\View\Element\Html\Select
{
    protected $tableColumnsSource;

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
        return $this->setName($value);
    }

    public function setInputId($value)
    {
        return $this->setId($value);
    }

    protected function _toHtml()
    {
        $allTablesColumns = $this->tableColumnsSource->getAllTablesColumns();
        
        if (!$this->getOptions()) {
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
