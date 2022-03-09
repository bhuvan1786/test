<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Custom\Blog\Model\ResourceModel\Blog\Relation\Store;

use Custom\Blog\Model\ResourceModel\Blog;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Framework\EntityManager\Operation\ExtensionInterface;

/**
 * Class ReadHandler
 */
class ReadHandler implements ExtensionInterface
{
    /**
     * @var MetadataPool
     */
    protected $metadataPool;

    /**
     * @var Page
     */
    protected $resourceBlog;

    /**
     * @param MetadataPool $metadataPool
     * @param Page $resourceBlog
     */
    public function __construct(
        MetadataPool $metadataPool,
        Blog $resourceBlog
    ) {
        $this->metadataPool = $metadataPool;
        $this->resourceBlog = $resourceBlog;
    }

    /**
     * @param object $entity
     * @param array $arguments
     * @return object
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function execute($entity, $arguments = [])
    {
        if ($entity->getId()) {
            $stores = $this->resourceBlog->lookupStoreIds((int)$entity->getId());
            $entity->setData('store_id', $stores);
        }
        return $entity;
    }
}
