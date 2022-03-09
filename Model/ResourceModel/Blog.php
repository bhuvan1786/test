<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Custom\Blog\Model\ResourceModel;

use Custom\Blog\Api\Data\BlogInterface;
use Custom\Blog\Model\Blog as CmsBlog;
use Magento\Framework\DB\Select;
use Magento\Framework\EntityManager\EntityManager;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\Framework\Model\ResourceModel\Db\Context;
use Magento\Framework\Stdlib\DateTime;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Cms page mysql resource
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Blog extends AbstractDb
{
    /**
     * Store model
     *
     * @var null|Store
     */
    protected $_store = null;

    /**
     * Store manager
     *
     * @var StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var DateTime
     */
    protected $dateTime;

    /**
     * @var EntityManager
     */
    protected $entityManager;

    /**
     * @var MetadataPool
     */
    protected $metadataPool;

    /**
     * @param Context $context
     * @param StoreManagerInterface $storeManager
     * @param DateTime $dateTime
     * @param EntityManager $entityManager
     * @param MetadataPool $metadataPool
     * @param string $connectionName
     */
    public function __construct(
        Context $context,
        StoreManagerInterface $storeManager,
        DateTime $dateTime,
        EntityManager $entityManager,
        MetadataPool $metadataPool,
        $connectionName = null
    ) {
        parent::__construct($context, $connectionName);
        $this->_storeManager = $storeManager;
        $this->dateTime = $dateTime;
        $this->entityManager = $entityManager;
        $this->metadataPool = $metadataPool;
    }

    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('cms_custom_blog', 'blog_id');
    }
 /**
     * Get store ids to which specified item is assigned
     *
     * @param int $pageId
     * @return array
     */
    public function lookupStoreIds($blogId)
    {
        $connection = $this->getConnection();

        $entityMetadata = $this->metadataPool->getMetadata(BlogInterface::class);
        $linkField = $entityMetadata->getLinkField();

        $select = $connection->select()
            ->from(['cps' => $this->getTable('cms_custom_blog_store')], 'store_id')
            ->join(
                ['cp' => $this->getMainTable()],
                'cps.' . $linkField . ' = cp.' . $linkField,
                []
            )
            ->where('cp.' . $entityMetadata->getIdentifierField() . ' = :blog_id');

        return $connection->fetchCol($select, ['blog_id' => (int)$blogId]);
    }

    /**
     * Check if page identifier exist for specific store
     * return page id if page exists
     *
     * @param string $identifier
     * @param int $storeId
     * @return int
     */
    public function checkIdentifier($identifier, $storeId)
    {
        $entityMetadata = $this->metadataPool->getMetadata(BlogInterface::class);

        $stores = [Store::DEFAULT_STORE_ID, $storeId];
        $select = $this->_getLoadByIdentifierSelect($identifier, $stores, 1);
        $select->reset(Select::COLUMNS)
            ->columns('cp.' . $entityMetadata->getIdentifierField())
            ->order('cps.store_id DESC')
            ->limit(1);

        return $this->getConnection()->fetchOne($select);
    }

/**
     * Process page data before saving
     *
     * @param AbstractModel $object
     * @return $this
     * @throws LocalizedException
     */
    protected function _beforeSave(AbstractModel $object)
    {
        /*
         * For two attributes which represent timestamp data in DB
         * we should make converting such as:
         * If they are empty we need to convert them into DB
         * type NULL so in DB they will be empty and not some default value
         */
       // echo "Sds";exit;
        foreach (['custom_theme_from', 'custom_theme_to'] as $field) {
            $value = !$object->getData($field) ? null : $this->dateTime->formatDate($object->getData($field));
            $object->setData($field, $value);
        }

        // if (!$this->isValidPageIdentifier($object)) {
        //     throw new LocalizedException(
        //         __(
        //             "The page URL key can't use capital letters or disallowed symbols. "
        //             . "Remove the letters and symbols and try again."
        //         )
        //     );
        // }

        // if ($this->isNumericPageIdentifier($object)) {
        //     throw new LocalizedException(
        //         __("The page URL key can't use only numbers. Add letters or words and try again.")
        //     );
        // }
        return parent::_beforeSave($object);
    }
    /**
     *  Check whether page identifier is numeric
     *
     * @param AbstractModel $object
     * @return bool
     */
    protected function isNumericPageIdentifier(AbstractModel $object)
    {
        return preg_match('/^[0-9]+$/', $object->getData('identifier'));
    }

    /**
     *  Check whether page identifier is valid
     *
     * @param AbstractModel $object
     * @return bool
     */
    protected function isValidPageIdentifier(AbstractModel $object)
    {
        return preg_match('/^[a-z0-9][a-z0-9_\/-]+(\.[a-z0-9_-]+)?$/', $object->getData('identifier'));
    }
    // /**
    //  * @param AbstractModel $object
    //  * @param string $value
    //  * @param string|null $field
    //  * @return bool|int|string
    //  * @throws LocalizedException
    //  * @throws \Exception
    //  */
    // private function getBlogId(AbstractModel $object, $value, $field = null)
    // {
    //     $entityMetadata = $this->metadataPool->getMetadata(BlogInterface::class);

    //     if (!is_numeric($value) && $field === null) {
    //         $field = 'identifier';
    //     } elseif (!$field) {
    //         $field = $entityMetadata->getIdentifierField();
    //     }

    //     $blogId = $value;
    //     if ($field != $entityMetadata->getIdentifierField() || $object->getStoreId()) {
    //         $select = $this->_getLoadSelect($field, $value, $object);
    //         $select->reset(Select::COLUMNS)
    //             ->columns($this->getMainTable() . '.' . $entityMetadata->getIdentifierField())
    //             ->limit(1);
    //         $result = $this->getConnection()->fetchCol($select);
    //         $blogId = count($result) ? $result[0] : false;
    //     }
    //     return $blogId;
    // }

    // /**
    //  * Load an object
    //  *
    //  * @param CmsPage|AbstractModel $object
    //  * @param mixed $value
    //  * @param string $field field to load by (defaults to model id)
    //  * @return $this
    //  */
    // public function load(AbstractModel $object, $value, $field = null)
    // {
    //     $blogId = $this->getBlogId($object, $value, $field);
    //     if ($blogId) {
    //         $this->entityManager->load($object, $blogId);
    //     }
    //     return $this;
    // }

    // /**
    //  * Retrieve select object for load object data
    //  *
    //  * @param string $field
    //  * @param mixed $value
    //  * @param CmsPage|AbstractModel $object
    //  * @return Select
    //  */
    // protected function _getLoadSelect($field, $value, $object)
    // {
    //     $entityMetadata = $this->metadataPool->getMetadata(BlogInterface::class);
    //     $linkField = $entityMetadata->getLinkField();

    //     $select = parent::_getLoadSelect($field, $value, $object);

    //     if ($object->getStoreId()) {
    //         $storeIds = [
    //             Store::DEFAULT_STORE_ID,
    //             (int)$object->getStoreId(),
    //         ];
    //         $select->join(
    //             ['cms_custom_blog_store' => $this->getTable('cms_custom_blog_store')],
    //             $this->getMainTable() . '.' . $linkField . ' = cms_custom_blog_store.' . $linkField,
    //             []
    //         )
    //             ->where('is_active = ?', 1)
    //             ->where('cms_custom_blog_store.store_id IN (?)', $storeIds)
    //             ->order('cms_custom_blog_store.store_id DESC')
    //             ->limit(1);
    //     }

    //     return $select;
    // }

    /**
     * Retrieve load select with filter by identifier, store and activity
     *
     * @param string $identifier
     * @param int|array $store
     * @param int $isActive
     * @return Select
     */
    protected function _getLoadByIdentifierSelect($identifier, $store, $isActive = null)
    {
        $entityMetadata = $this->metadataPool->getMetadata(BlogInterface::class);
        $linkField = $entityMetadata->getLinkField();

        $select = $this->getConnection()->select()
            ->from(['cp' => $this->getMainTable()])
            ->join(
                ['cps' => $this->getTable('cms_custom_blog_store')],
                'cp.' . $linkField . ' = cps.' . $linkField,
                []
            )
            ->where('cp.identifier = ?', $identifier)
            ->where('cps.store_id IN (?)', $store);

        if ($isActive !== null) {
            $select->where('cp.is_active = ?', $isActive);
        }

        return $select;
    }




    /**
     * Set store model
     *
     * @param Store $store
     * @return $this
     */
    public function setStore($store)
    {
        $this->_store = $store;
        return $this;
    }

    /**
     * Retrieve store model
     *
     * @return Store
     */
    public function getStore()
    {
        return $this->_storeManager->getStore($this->_store);
    }
// to do
    /**
     * @inheritDoc
     */
    public function save(AbstractModel $object)
    {

        $this->entityManager->save($object);
        return $this;
     }
    /**
     * @inheritDoc
     */
    public function delete(AbstractModel $object)
    {
        $this->entityManager->delete($object);
        return $this;
    }
}
