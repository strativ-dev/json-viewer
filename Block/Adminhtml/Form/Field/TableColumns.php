<?php
namespace Strativ\JsonViewer\Block\Adminhtml\Form\Field;

use Magento\Config\Block\System\Config\Form\Field\FieldArray\AbstractFieldArray;

class TableColumns extends AbstractFieldArray
{
    protected function _prepareToRender()
    {
        $this->addColumn('table_name', [
            'label' => __('Table Name'),
            'class' => 'required-entry'
        ]);
        
        $this->addColumn('columns', [
            'label' => __('Columns'),
            'class' => 'required-entry'
        ]);

        $this->_addAfter = false;
        $this->_addButtonLabel = __('Add Table');
    }
}