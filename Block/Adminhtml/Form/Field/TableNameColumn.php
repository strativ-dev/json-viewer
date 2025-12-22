<?php
namespace Strativ\JsonViewer\Block\Adminhtml\Form\Field;

use Magento\Framework\View\Element\Html\Select;
use Strativ\JsonViewer\Model\Config\Source\DatabaseTables;

class TableNameColumn extends Select
{
    protected $databaseTables;

    public function __construct(
        \Magento\Framework\View\Element\Context $context,
        DatabaseTables $databaseTables,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->databaseTables = $databaseTables;
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
        $this->setOptions($this->databaseTables->toOptionArray());
        $this->setClass('admin__control-select');
        return parent::_toHtml();
    }
}
