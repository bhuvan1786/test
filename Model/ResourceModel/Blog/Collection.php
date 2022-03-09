<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Custom\Blog\Model\ResourceModel\Blog;

use Custom\Blog\Api\Data\BlogInterface;
use Custom\Blog\Model\ResourceModel\AbstractCollection;
//\Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
/**
 * CMS page collection
 */
class Collection extends AbstractCollection
{
    /**
     * @var string
     */
    protected $_idFieldName = 'blog_id';

    /**
     * Load data for preview flag
     *
     * @var bool
     */
    protected $_previewFlag;

    /**
     * Event prefix
     *
     * @var string
     */
    protected $_eventPrefix = 'cms_blog_collection';

    /**
     * Event object
     *
     * @var string
     */
    protected $_eventObject = 'blog_collection';

    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(\Custom\Blog\Model\Blog::class, \Custom\Blog\Model\ResourceModel\Blog::class);
        $this->_map['fields']['blog_id'] = 'main_table.blog_id';
        $this->_map['fields']['store'] = 'store_table.store_id';
    }

  /**
     * Add filter by store
     *
     * @param int|array|\Magento\Store\Model\Store $store
     * @param bool $withAdmin
     * @return $this
     */
    public function addStoreFilter($store, $withAdmin = true)
    {
        if (!$this->getFlag('store_filter_added')) {
            $this->performAddStoreFilter($store, $withAdmin);
            $this->setFlag('store_filter_added', true);
        }

        return $this;
    }
   /**
     * Perform operations after collection load
     *
     * @return $this
     */
    protected function _afterLoad()
    {   
        $entityMetadata = $this->metadataPool->getMetadata(BlogInterface::class);
        $this->performAfterLoad('cms_custom_blog_store', $entityMetadata->getLinkField());
        $this->_previewFlag = false;

        return parent::_afterLoad();
    }
}
