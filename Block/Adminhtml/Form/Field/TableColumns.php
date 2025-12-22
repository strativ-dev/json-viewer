<?php
namespace Strativ\JsonViewer\Block\Adminhtml\Form\Field;

use Magento\Config\Block\System\Config\Form\Field\FieldArray\AbstractFieldArray;
use Magento\Framework\DataObject;
use Magento\Framework\Exception\LocalizedException;

class TableColumns extends AbstractFieldArray
{
    private $tableNameRenderer;
    private $columnMultiselectRenderer;

    protected function _prepareToRender()
    {
        $this->addColumn('table_name', [
            'label' => __('Table Name'),
            'class' => 'required-entry',
            'renderer' => $this->getTableNameRenderer()
        ]);
        
        $this->addColumn('columns', [
            'label' => __('Columns'),
            'class' => 'required-entry',
            'renderer' => $this->getColumnMultiselectRenderer()
        ]);

        $this->_addAfter = false;
        $this->_addButtonLabel = __('Add Table');
    }

    private function getTableNameRenderer()
    {
        if (!$this->tableNameRenderer) {
            $this->tableNameRenderer = $this->getLayout()->createBlock(
                TableNameColumn::class,
                '',
                ['data' => ['is_render_to_js_template' => true]]
            );
        }
        return $this->tableNameRenderer;
    }

    private function getColumnMultiselectRenderer()
    {
        if (!$this->columnMultiselectRenderer) {
            $this->columnMultiselectRenderer = $this->getLayout()->createBlock(
                ColumnMultiselect::class,
                '',
                ['data' => ['is_render_to_js_template' => true]]
            );
        }
        return $this->columnMultiselectRenderer;
    }

    protected function _prepareArrayRow(DataObject $row)
    {
        $options = [];
        $tableName = $row->getData('table_name');
        if ($tableName !== null) {
            $options['option_' . $this->getTableNameRenderer()->calcOptionHash($tableName)] = 'selected="selected"';
        }
        $row->setData('option_extra_attrs', $options);
    }
}