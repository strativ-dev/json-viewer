<?php

namespace Strativ\JsonViewer\Block\Adminhtml\View\Renderer;

use Magento\Backend\Block\Context;
use Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer;
use Magento\Framework\DataObject;
use Magento\Framework\Escaper;

class JsonText extends AbstractRenderer
{
    /**
     * @var Escaper
     */
    private Escaper $escaper;

    /**
     * @param Context $context
     * @param Escaper $escaper
     * @param array $data
     */
    public function __construct(
        Context $context,
        Escaper $escaper,
        array   $data = []
    ) {
        $this->escaper = $escaper;
        parent::__construct($context, $data);
    }

    /**
     * Render the JSON text
     *
     * @param DataObject $row
     * @return string
     */
    public function render(DataObject $row): string
    {
        $value = $row->getData($this->getColumn()->getIndex());

        if (empty($value)) {
            return '';
        }

        if ($this->isJson($value)) {
            $decoded = json_decode($value, true);
            if ($decoded !== null) {
                $formattedJson = json_encode($decoded, JSON_PRETTY_PRINT);
                $escapedJson = $this->escaper->escapeHtml($formattedJson);
                $uniqueId = 'json_' . hash('sha256', $value . rand());

                return '<button type="button" class="action-default scalable" '
                    . 'onclick="showJsonModal(\'' . $uniqueId . '\')" '
                    . 'style="padding: 5px 10px;">'
                    . '<span>View JSON</span>'
                    . '</button>'
                    . '<div id="' . $uniqueId . '" style="display:none;">' . $escapedJson . '</div>';
            }
        }

        $displayText = strlen($value) > 25 ? substr($value, 0, 25) . '...' : $value;
        return '<span>' . $this->escaper->escapeHtml($displayText) . '</span>';
    }

    /**
     * Check if a string is valid JSON
     *
     * @param mixed $string
     * @return bool
     */
    protected function isJson(mixed $string): bool
    {
        if (!is_string($string)) {
            return false;
        }

        json_decode($string);
        return json_last_error() === JSON_ERROR_NONE;
    }
}
