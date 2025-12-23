<?php
namespace Strativ\JsonViewer\Block\Adminhtml\View\Renderer;

use Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer;
use Magento\Framework\DataObject;

class JsonText extends AbstractRenderer
{
    public function render(DataObject $row)
    {
        $value = $row->getData($this->getColumn()->getIndex());
        
        if (empty($value)) {
            return '';
        }

        if ($this->isJson($value)) {
            $decoded = json_decode($value, true);
            if ($decoded !== null) {
                $formattedJson = json_encode($decoded, JSON_PRETTY_PRINT);
                $escapedJson = htmlspecialchars($formattedJson);
                $uniqueId = 'json_' . md5($value . rand());
                
                return '<button type="button" class="action-default scalable" '
                    . 'onclick="showJsonModal(\'' . $uniqueId . '\')" '
                    . 'style="padding: 5px 10px;">'
                    . '<span>View JSON</span>'
                    . '</button>'
                    . '<div id="' . $uniqueId . '" style="display:none;">' . $escapedJson . '</div>';
            }
        }

        $displayText = strlen($value) > 25 ? substr($value, 0, 25) . '...' : $value;
        return '<span>' . htmlspecialchars($displayText) . '</span>';
    }

    protected function isJson($string)
    {
        if (!is_string($string)) {
            return false;
        }
        
        json_decode($string);
        return (json_last_error() == JSON_ERROR_NONE);
    }
}
