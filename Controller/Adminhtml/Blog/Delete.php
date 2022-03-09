<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Custom\Blog\Controller\Adminhtml\Blog;

use Magento\Framework\App\Action\HttpPostActionInterface;

/**
 * Delete CMS page action.
 */
class Delete extends \Magento\Backend\App\Action implements HttpPostActionInterface
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Magento_Cms::blog_delete';

    /**
     * Delete action
     *
     * @return \Magento\Backend\Model\View\Result\Redirect
     */
    public function execute()
    {
        // check if we know what should be deleted
        $id = $this->getRequest()->getParam('blog_id');
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
    // echo "<pre>";print_R( get_class_methods($resultRedirect));exit;
        if ($id) {
            $title = "";
            try {
                // init model and delete
                $model = $this->_objectManager->create(\Custom\Blog\Model\Blog::class);
                $model->load($id);
                $title = $model->getTitle();
                $model->delete();
                
                // display success message
                $this->messageManager->addSuccessMessage(__('The blog has been deleted.'));
                
                // // go to grid
                // $this->_eventManager->dispatch('adminhtml_cmspage_on_delete', [
                //     'title' => $title,
                //     'status' => 'success'
                // ]);
                
                return $resultRedirect->setPath('*/*/');
            } catch (\Exception $e) {
                // $this->_eventManager->dispatch(
                //     'adminhtml_cmspage_on_delete',
                //     ['title' => $title, 'status' => 'fail']
                // );
                // display error message
                $this->messageManager->addErrorMessage($e->getMessage());
                // go back to edit form
                return $resultRedirect->setPath('*/*/edit', ['blog_id' => $id]);
            }
        }
        
        // display error message
        $this->messageManager->addErrorMessage(__('We can\'t find a blog to delete.'));
        
        // go to grid
        return $resultRedirect->setPath('*/*/');
    }
}
