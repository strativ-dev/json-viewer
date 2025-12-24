<?php

namespace Strativ\JsonViewer\Block\Adminhtml\Form\Field;

use Magento\Framework\View\Element\Context;
use Magento\Framework\View\Element\Html\Select;
use Strativ\JsonViewer\Model\Config\Source\DatabaseTables;

class TableNameColumn extends Select
{
    /** @var DatabaseTables */
    protected DatabaseTables $databaseTables;

    /**
     * Constructor
     *
     * @param Context $context
     * @param DatabaseTables $databaseTables
     * @param array $data
     */
    public function __construct(
        Context        $context,
        DatabaseTables $databaseTables,
        array          $data = []
    )
    {
        parent::__construct($context, $data);
        $this->databaseTables = $databaseTables;
    }

    /**
     * Set the input name for the select
     *
     * @param string $value
     * @return $this
     */
    public function setInputName($value): self
    {
        return $this->setName($value);
    }

    /**
     * Set the input id for the select
     *
     * @param string $value
     * @return $this
     */
    public function setInputId(string $value): self
    {
        return $this->setId($value);
    }

    /**
     * Render the HTML for the select element
     *
     * @return string
     */
    protected function _toHtml(): string
    {
        $this->setOptions($this->databaseTables->toOptionArray());
        $this->setClass('admin__control-select');
        return parent::_toHtml();
    }
}
