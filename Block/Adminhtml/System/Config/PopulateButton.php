<?php
namespace Strativ\JsonViewer\Block\Adminhtml\System\Config;

use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Framework\App\Config\ScopeConfigInterface;

class PopulateButton extends Field
{
    protected $_template = 'Strativ_JsonViewer::system/config/populate_button.phtml';
    protected $scopeConfig;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->scopeConfig = $context->getScopeConfig();
    }

    protected function _getElementHtml(AbstractElement $element)
    {
        return $this->_toHtml();
    }

    public function getButtonHtml()
    {
        $button = $this->getLayout()->createBlock(
            \Magento\Backend\Block\Widget\Button::class
        )->setData([
            'id' => 'populate_tables_button',
            'label' => __('Populate Table Columns'),
            'onclick' => 'javascript:populateTableColumns(); return false;'
        ]);

        return $button->toHtml();
    }

    public function getSavedTableColumns()
    {
        $savedData = $this->scopeConfig->getValue(
            'strativ_jsonviewer/general/table_columns',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
        
        if ($savedData) {
            $data = json_decode($savedData, true);
            if (is_array($data)) {
                return json_encode($data);
            }
        }
        
        return '{}';
    }
}