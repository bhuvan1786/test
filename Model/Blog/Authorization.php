<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Custom\Blog\Model\Blog;

use Custom\Blog\Api\Data\BlogInterface;
use Custom\Blog\Api\BlogRepositoryInterface;
use Magento\Framework\AuthorizationInterface;
use Magento\Framework\Exception\AuthorizationException;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;
use \Magento\Store\Model\StoreManagerInterface;

/**
 * Authorization for saving a page.
 */
class Authorization
{
    /**
     * @var BlogRepositoryInterface
     */
    private $blogRepository;

    /**
     * @var AuthorizationInterface
     */
    private $authorization;

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @param BlogRepositoryInterface $blogRepository
     * @param AuthorizationInterface $authorization
     * @param ScopeConfigInterface $scopeConfig
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        BlogRepositoryInterface $blogRepository,
        AuthorizationInterface $authorization,
        ScopeConfigInterface $scopeConfig,
        StoreManagerInterface $storeManager
    ) {
        $this->blogRepository = $blogRepository;
        $this->authorization = $authorization;
        $this->scopeConfig = $scopeConfig;
        $this->storeManager = $storeManager;
    }

    /**
     * Authorize user before updating a page.
     *
     * @param PageInterface $page
     * @return void
     * @throws AuthorizationException
     * @throws \Magento\Framework\Exception\LocalizedException When it is impossible to perform authorization.
     */
    public function authorizeFor(BlogInterface $blog): void
    {
        //Validate design changes.
        if (!$this->authorization->isAllowed('Magento_Cms::save_design')) {
            $oldblog = null;
            if ($blog->getId()) {
                $oldblog = $this->blogRepository->getById($blog->getId());
            }
            if ($this->hasPageChanged($blog, $oldblog)) {
                throw new AuthorizationException(
                    __('You are not allowed to change CMS blogs design settings')
                );
            }
        }
    }
}
