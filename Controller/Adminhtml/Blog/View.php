<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Custom\Blog\Controller\Adminhtml\Blog;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\View\Result\Page;
use Magento\Framework\View\Result\PageFactory;

/**
 * Class Index
 */
class View extends Action implements HttpGetActionInterface
{
    const MENU_ID = 'Custom_Blog::customcms_blog';

    /**
     * @var PageFactory
     */
    protected $resultPageFactory;
    /**
     * @var \Magento\Backend\Model\View\Result\Forward
     */
    protected $resultForwardFactory;

    /**
     * Index constructor.
     *
     * @param Context $context
     * @param PageFactory $resultPageFactory
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        \Magento\Backend\Model\View\Result\ForwardFactory $resultForwardFactory

    ) {
        parent::__construct($context);
        $this->resultForwardFactory = $resultForwardFactory;
        $this->resultPageFactory = $resultPageFactory;
    }

    /**
     * Load the page defined in view/adminhtml/layout/exampleadminnewpage_helloworld_index.xml
     *
     * @return Page
     */
    public function execute()
    {
       // $this->_view->loadLayout();
       // $this->_view->renderLayout();
		// echo "Hello World";
		// exit;
    //    $resultForward = $this->resultPageFactory->create();
    //    return $resultForward;
       $resultForward = $this->resultForwardFactory->create();
       return $resultForward->forward('edit');

        //return $resultForward->forward('edit');
        // $resultPage = $this->resultPageFactory->create();
        // $resultPage->setActiveMenu(static::MENU_ID);
        // $resultPage->getConfig()->getTitle()->prepend(__('Hello Worldxcxcxc'));

        // return $resultPage;
    }
}


