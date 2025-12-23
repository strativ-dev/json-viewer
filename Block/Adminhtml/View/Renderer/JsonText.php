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
                return '<pre style="max-width: 400px; overflow: auto; white-space: pre-wrap;">' 
                    . htmlspecialchars(json_encode($decoded, JSON_PRETTY_PRINT)) 
                    . '</pre>';
            }
        }

        if (strlen($value) > 100) {
            return '<div style="max-width: 400px; overflow: auto; word-wrap: break-word;">' 
                . htmlspecialchars($value) 
                . '</div>';
        }

        return htmlspecialchars($value);
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
