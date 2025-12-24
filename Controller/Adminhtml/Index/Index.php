<?php

namespace Strativ\JsonViewer\Controller\Adminhtml\Index;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\View\Result\Page;

class Index extends Action implements HttpGetActionInterface
{
    /** @var PageFactory */
    protected PageFactory $resultPageFactory;

    /**
     * Constructor
     *
     * @param Context $context
     * @param PageFactory $resultPageFactory
     */
    public function __construct(
        Context     $context,
        PageFactory $resultPageFactory
    )
    {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
    }

    /**
     * Execute the action
     *
     * @return Page
     */
    public function execute(): Page
    {
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('Strativ_JsonViewer::jsonviewer');
        $resultPage->getConfig()->getTitle()->prepend(__('Json Viewer'));

        return $resultPage;
    }

    /**
     * Check if the user has permission to access this action
     *
     * @return bool
     */
    protected function _isAllowed(): bool
    {
        return $this->_authorization->isAllowed('Strativ_JsonViewer::jsonviewer');
    }
}
