<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Custom\Blog\Controller\Adminhtml\Blog;

use Magento\Backend\App\Action\Context;
use Custom\Blog\Api\BlogRepositoryInterface as BlogRepository;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\Controller\Result\JsonFactory;
use Custom\Blog\Api\Data\BlogInterface;

/**
 * Cms page grid inline edit controller
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class InlineEdit extends \Magento\Backend\App\Action implements HttpPostActionInterface
{
    /**
     * Authorization level of a basic admin session
     */
    const ADMIN_RESOURCE = 'Nagarro_Cms::save';

    /**
     * @var \Magento\Cms\Controller\Adminhtml\Page\PostDataProcessor
     */
    protected $dataProcessor;

    /**
     * @var \Magento\Cms\Api\BlogRepositoryInterface
     */
    protected $blogRepository;

    /**
     * @var \Magento\Framework\Controller\Result\JsonFactory
     */
    protected $jsonFactory;

    /**
     * @param Context $context
     * @param PostDataProcessor $dataProcessor
     * @param BlogRepository $blogRepository
     * @param JsonFactory $jsonFactory
     */
    public function __construct(
        Context $context,
        PostDataProcessor $dataProcessor,
        BlogRepository $blogRepository,
        JsonFactory $jsonFactory
    ) {
        parent::__construct($context);
        $this->dataProcessor = $dataProcessor;
        $this->blogRepository = $blogRepository;
        $this->jsonFactory = $jsonFactory;
    }

    /**
     * Process the request
     *
     * @return \Magento\Framework\Controller\ResultInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function execute()
    {
        /** @var \Magento\Framework\Controller\Result\Json $resultJson */
        $resultJson = $this->jsonFactory->create();
        $error = false;
        $messages = [];
        $postItems = $this->getRequest()->getParam('items', []);

        if (!($this->getRequest()->getParam('isAjax') && count($postItems))) {
            return $resultJson->setData(
                [
                    'messages' => [__('Please correct the data sent.')],
                    'error' => true,
                ]
            );
        }

        foreach (array_keys($postItems) as $blogId) {
            /** @var \Nagarro\CustomBlog\Model\Blog $page */
            $blog = $this->blogRepository->getById($blogId);
            try {
                $extendedBlogData =  $blog->getData();
                $blogData = $this->filterPostWithDateConverting($postItems[$blogId], $extendedBlogData);
              //  $this->validatePost($blogData, $blog, $error, $messages);  //to do
                $this->setCmsBlogData($blog, $extendedBlogData, $blogData);
                $this->blogRepository->save($blog);
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $messages[] = $this->getErrorWithPageId($blog, $e->getMessage());
                $error = true;
            } catch (\RuntimeException $e) {
                $messages[] = $this->getErrorWithPageId($blog, $e->getMessage());
                $error = true;
            } catch (\Exception $e) {
                $messages[] = $this->getErrorWithPageId(
                    $blog,
                    __('Something went wrong while saving the page.')
                );
                $error = true;
            }
        }

        return $resultJson->setData(
            [
                'messages' => $messages,
                'error' => $error
            ]
        );
    }

    /**
     * Filtering posted data.
     *
     * @param array $postData
     * @return array
     */
    protected function filterPost($postData = [])
    {
        $pageData = $this->dataProcessor->filter($postData);
        $pageData['custom_theme'] = isset($pageData['custom_theme']) ? $pageData['custom_theme'] : null;
        $pageData['custom_root_template'] = isset($pageData['custom_root_template'])
            ? $pageData['custom_root_template']
            : null;
        return $pageData;
    }

    /**
     * Filtering posted data with converting custom theme dates to proper format
     *
     * @param array $postData
     * @param array $pageData
     * @return array
     */
    private function filterPostWithDateConverting($postData = [], $pageData = [])
    {
        $newPageData = $this->filterPost($postData);
        if (
            !empty($newPageData['custom_theme_from'])
            && date("Y-m-d", strtotime($postData['custom_theme_from']))
                === date("Y-m-d", strtotime($pageData['custom_theme_from']))
        ) {
            $newPageData['custom_theme_from'] = date("Y-m-d", strtotime($postData['custom_theme_from']));
        }
        if (
            !empty($newPageData['custom_theme_to'])
            && date("Y-m-d", strtotime($postData['custom_theme_to']))
                === date("Y-m-d", strtotime($pageData['custom_theme_to']))
        ) {
            $newPageData['custom_theme_to'] = date("Y-m-d", strtotime($postData['custom_theme_to']));
        }

        return $newPageData;
    }

    /**
     * Validate post data
     *
     * @param array $pageData
     * @param \Magento\Cms\Model\Page $page
     * @param bool $error
     * @param array $messages
     * @return void
     */
    protected function validatePost(array $pageData, \Magento\Cms\Model\Page $page, &$error, array &$messages)
    {
        if (!$this->dataProcessor->validateRequireEntry($pageData)) {
            $error = true;
            foreach ($this->messageManager->getMessages(true)->getItems() as $error) {
                $messages[] = $this->getErrorWithPageId($page, $error->getText());
            }
        }
    }

    /**
     * Add page title to error message
     *
     * @param BlogInterface $page
     * @param string $errorText
     * @return string
     */
    protected function getErrorWithPageId(BlogInterface $page, $errorText)
    {
        return '[Page ID: ' . $page->getId() . '] ' . $errorText;
    }

    /**
     * Set cms page data
     *
     * @param \Magento\Cms\Model\Page $page
     * @param array $extendedBlogData
     * @param array $pageData
     * @return $this
     */
    public function setCmsBlogData(\Custom\Blog\Model\Blog $blog, array $extendedBlogData, array $blogData)
    {
        $blog->setData(array_merge($blog->getData(), $extendedBlogData, $blogData));
        return $this;
    }
}
