<?php

namespace Strativ\JsonViewer\Block\Adminhtml\Form\Field;

use Magento\Config\Block\System\Config\Form\Field\FieldArray\AbstractFieldArray;
use Magento\Framework\DataObject;
use Magento\Framework\Exception\LocalizedException;

class TableColumns extends AbstractFieldArray
{
    /**
     * @var TableNameColumn|null
     */
    private ?TableNameColumn $tableNameRenderer = null;

    /**
     * @var ColumnMultiselect|null
     */
    private ?ColumnMultiselect $columnMultiselectRenderer = null;

    /**
     * Prepare to render the table columns field array
     *
     * @return void
     * @throws LocalizedException
     */
    protected function _prepareToRender(): void
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

    /**
     * Get the table name renderer block
     *
     * @return TableNameColumn
     * @throws LocalizedException
     */
    private function getTableNameRenderer(): TableNameColumn
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

    /**
     * Get the column multiselect renderer block
     *
     * @return ColumnMultiselect
     * @throws LocalizedException
     */
    private function getColumnMultiselectRenderer(): ColumnMultiselect
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

    /**
     * Prepare each row of the field array
     *
     * @param DataObject $row
     * @return void
     * @throws LocalizedException
     */
    protected function _prepareArrayRow(DataObject $row): void
    {
        $options = [];

        $tableName = $row->getData('table_name');
        if ($tableName !== null) {
            $options['option_' . $this->getTableNameRenderer()->calcOptionHash($tableName)] = 'selected="selected"';
        }

        $columns = $row->getData('columns');
        if (!empty($columns)) {
            if (!is_array($columns)) {
                $columns = is_string($columns) ? explode(',', $columns) : [$columns];
            }

            // Set the table name on the column renderer so it can render the correct options
            $columnRenderer = $this->getColumnMultiselectRenderer();
            $columnRenderer->setTableName($tableName);

            foreach ($columns as $column) {
                if (!empty($column)) {
                    $options['option_' . $columnRenderer->calcOptionHash($column)] = 'selected="selected"';
                }
            }
        }

        $row->setData('option_extra_attrs', $options);
    }
}
