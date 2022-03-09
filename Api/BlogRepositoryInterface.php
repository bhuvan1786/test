<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Custom\Blog\Api;

use Magento\Framework\Api\SearchCriteriaInterface;

/**
 * CMS page CRUD interface.
 * @api
 * @since 100.0.2
 */
interface BlogRepositoryInterface
{
    /**
     * Save page.
     *
     * @param \Custom\Blog\Api\Data\BlogInterface $blog
     * @return \Custom\Blog\Api\Data\BlogInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function save(\Custom\Blog\Api\Data\BlogInterface $blog);

    /**
     * Retrieve page.
     *
     * @param int $blogId
     * @return \Magento\Cms\Api\Data\PageInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getById($blogId);


}
