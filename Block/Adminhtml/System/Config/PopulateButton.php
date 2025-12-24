<?php

namespace Strativ\JsonViewer\Block\Adminhtml\System\Config;

use Magento\Backend\Block\Template\Context;
use Magento\Backend\Block\Widget\Button;
use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Exception\LocalizedException;

class PopulateButton extends Field
{
    /**
     * @var string
     */
    protected $_template = 'Strativ_JsonViewer::system/config/populate_button.phtml';
    /**
     * @var ScopeConfigInterface
     */
    protected ScopeConfigInterface $scopeConfig;

    /**
     * Constructor
     *
     * @param Context $context
     * @param array $data
     */
    public function __construct(
        Context $context,
        array   $data = []
    )
    {
        parent::__construct($context, $data);
        $this->scopeConfig = $context->getScopeConfig();
    }

    /**
     * Render the button HTML
     *
     * @param AbstractElement $element
     * @return string
     */
    protected function _getElementHtml(AbstractElement $element): string
    {
        return $this->_toHtml();
    }

    /**
     * Generate the button HTML
     *
     * @return string
     * @throws LocalizedException
     */
    public function getButtonHtml(): string
    {
        $button = $this->getLayout()->createBlock(
            Button::class
        )->setData([
            'id' => 'populate_tables_button',
            'label' => __('Populate Table Columns'),
            'onclick' => 'javascript:populateTableColumns(); return false;'
        ]);

        return $button->toHtml();
    }

    /**
     * Retrieve saved table columns configuration
     *
     * @return string
     */
    public function getSavedTableColumns(): string
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
