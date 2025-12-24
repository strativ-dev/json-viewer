<?php
namespace Strativ\JsonViewer\Block\Adminhtml;

use Magento\Backend\Block\Widget\Grid\Container;

class View extends Container
{
    /**
     * Initialize View Container
     */
    protected function _construct(): void
    {
        $this->_controller = 'adminhtml_view';
        $this->_blockGroup = 'Strativ_JsonViewer';
        $this->_headerText = __('Table Data');
        
        parent::_construct();
        $this->removeButton('add');
        
        $this->addButton(
            'back',
            [
                'label' => __('Back'),
                'onclick' => 'setLocation(\'' . $this->getUrl('*/*/index') . '\')',
                'class' => 'back'
            ],
            -1
        );
    }
}
