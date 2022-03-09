<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Custom\Blog\Controller\Adminhtml\Blog;

use Magento\Backend\App\Action;
use Magento\Backend\Model\View\Result\Redirect;
use Magento\Cms\Api\Data\PageInterface;
//use Magento\Cms\Api\PageRepositoryInterface;
//use Magento\Cms\Model\Page;
//use Magento\Cms\Model\PageFactory;
use Custom\Blog\Model\Blog;
use Custom\Blog\Model\BlogFactory;
use Custom\Blog\Api\BlogRepositoryInterface;


use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\LocalizedException;

/**
 * Save CMS page action.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Save extends Action implements HttpPostActionInterface
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    //const ADMIN_RESOURCE = 'Magento_Cms::save';

     /**
      * @var PostDataProcessor
      */
     protected $dataProcessor;

    /**
     * @var DataPersistorInterface
     */
    protected $dataPersistor;

    /**
     * @var BlogFactory
     */
    private $blogFactory;

    /**
     * @var BlogRepositoryInterface
     */
    private $blogRepository;

    /**
     * @param Action\Context $context
     * @param PostDataProcessor $dataProcessor
     * @param DataPersistorInterface $dataPersistor
     * @param BlogFactory|null $blogFactory
     * @param BlogRepositoryInterface|null $blogRepository
     */
    public function __construct(
        Action\Context $context,
        PostDataProcessor $dataProcessor,
        DataPersistorInterface $dataPersistor,
        BlogFactory $blogFactory = null,
        BlogRepositoryInterface $blogRepository = null
    ) {
        $this->dataProcessor = $dataProcessor;
        $this->dataPersistor = $dataPersistor;
        //$this->blogFactory = $blogFactory
        $this->blogFactory = $blogFactory ?: ObjectManager::getInstance()->get(BlogFactory::class);
        $this->blogRepository = $blogRepository ?: ObjectManager::getInstance()->get(BlogRepositoryInterface::class);
       // $this->blogRepository = $blogRepository;
        parent::__construct($context);
    }

    /**
     * Save action
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @return ResultInterface
     */
    public function execute()
    {
        $data = $this->getRequest()->getPostValue();

        /** @var Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();

        if ($data) {
            $data = $this->dataProcessor->filter($data); 
           // echo "<pre>";print_R( get_class_methods($resultRedirect));exit;
         // echo "<pre>ff";print_R($data);exit;

            if (isset($data['is_active']) && $data['is_active'] === 'true') {
                $data['is_active'] = Blog::STATUS_ENABLED;
            }
            if (empty($data['blog_id'])) {
                $data['blog_id'] = null;
            }

            /** @var Page $model */
            $model = $this->blogFactory->create();
            $id = $this->getRequest()->getParam('blog_id');
            if ($id) {
                try {
                    $model = $this->blogRepository->getById($id);
                } catch (LocalizedException $e) {
                    $this->messageManager->addErrorMessage(__('This page no longer exists.'));
                    return $resultRedirect->setPath('*/*/');
                }
            }

            $data['layout_update_xml'] = $model->getLayoutUpdateXml();
            $data['custom_layout_update_xml'] = $model->getCustomLayoutUpdateXml();
            $model->setData($data);

            try {

                $this->_eventManager->dispatch(
                    'cms_blog_prepare_save',
                    ['blog' => $model, 'request' => $this->getRequest()]
                );

                $this->blogRepository->save($model);

                $this->messageManager->addSuccessMessage(__('You saved the blog.'));
                return $this->processResultRedirect($model, $resultRedirect, $data);
            } catch (LocalizedException $e) {
                $this->messageManager->addExceptionMessage($e->getPrevious() ?: $e);
            } catch (\Throwable $e) {
                $this->messageManager->addErrorMessage(__('Something went wrong while saving the blog.'));
            }

            $this->dataPersistor->set('cms_blog', $data);
            return $resultRedirect->setPath('*/*/edit', ['blog_id' => $this->getRequest()->getParam('blog_id')]);
        }
        return $resultRedirect->setPath('*/*/');
    }

   /**
     * Process result redirect
     *
     * @param PageInterface $model
     * @param Redirect $resultRedirect
     * @param array $data
     * @return Redirect
     * @throws LocalizedException
     */
    private function processResultRedirect($model, $resultRedirect, $data)
    {

        if ($this->getRequest()->getParam('back', false) === 'duplicate') {
            $newPage = $this->pageFactory->create(['data' => $data]);
            $newPage->setId(null);
            $identifier = $model->getIdentifier() . '-' . uniqid();
            $newPage->setIdentifier($identifier);
            $newPage->setIsActive(false);
            $this->pageRepository->save($newPage);
            $this->messageManager->addSuccessMessage(__('You duplicated the page.'));
            return $resultRedirect->setPath(
                '*/*/edit',
                [
                    'page_id' => $newPage->getId(),
                    '_current' => true,
                ]
            );
        }

        $this->dataPersistor->clear('cms_blog');
        if ($this->getRequest()->getParam('back')) {
            return $resultRedirect->setPath('*/*/edit', ['blog_id' => $model->getId(), '_current' => true]);
        }
        return $resultRedirect->setPath('*/*/');
    }
}
