<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Custom\Blog\Model;

use Custom\Blog\Api\Data\BlogInterface;
use Magento\Cms\Helper\Page as PageHelper;
use Magento\Cms\Model\Page\CustomLayout\CustomLayoutRepository;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\DataObject\IdentityInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Validation\ValidationException;
use Magento\Framework\Validator\HTML\WYSIWYGValidatorInterface;
use Magento\Backend\Model\Validator\UrlKey\CompositeUrlKey;

/**
 * Cms Page Model
 *
 * @api
 * @method Page setStoreId(int $storeId)
 * @method int getStoreId()
 * @SuppressWarnings(PHPMD.ExcessivePublicCount)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @since 100.0.2
 */
class Blog extends AbstractModel implements BlogInterface, IdentityInterface
{
    /**
     * Page ID for the 404 page.
     */
    const NOROUTE_PAGE_ID = 'no-route';

    /**#@+
     * Page's Statuses
     */
    const STATUS_ENABLED = 1;
    const STATUS_DISABLED = 0;
    /**#@-*/

    /**
     * CMS blog cache tag
     */
    const CACHE_TAG = 'cms_b';

    /**
     * @var string
     */
    protected $_cacheTag = self::CACHE_TAG;

    /**
     * Prefix of model events names
     *
     * @var string
     */
    protected $_eventPrefix = 'cms_blog';

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var CustomLayoutRepository
     */
    private $customLayoutRepository;

    /**
     * @var WYSIWYGValidatorInterface
     */
    private $wysiwygValidator;

    /**
     * @var CompositeUrlKey
     */
    private $compositeUrlValidator;

    /**
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource|null $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb|null $resourceCollection
     * @param array $data
     * @param CustomLayoutRepository|null $customLayoutRepository
     * @param WYSIWYGValidatorInterface|null $wysiwygValidator
     * @param CompositeUrlKey|null $compositeUrlValidator
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = [],
        ?CustomLayoutRepository $customLayoutRepository = null,
        ?WYSIWYGValidatorInterface $wysiwygValidator = null
        //\Magento\Backend\Model\Validator\UrlKey\CompositeUrlKey $compositeUrlValidator = null
    ) {
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
       // echo "trtrtmodel";exit;

        $this->customLayoutRepository = $customLayoutRepository
            ?? ObjectManager::getInstance()->get(CustomLayoutRepository::class);
        $this->wysiwygValidator = $wysiwygValidator
            ?? ObjectManager::getInstance()->get(WYSIWYGValidatorInterface::class);
        //$this->compositeUrlValidator = $compositeUrlValidator
            //?? ObjectManager::getInstance()->get(CompositeUrlKey::class);
    }

    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(\Custom\Blog\Model\ResourceModel\Blog::class);
    }

    // /**
    //  * Load object data
    //  *
    //  * @param int|null $id
    //  * @param string $field
    //  * @return $this
    //  */
    public function load($id, $field = null)
    {
        if ($id === null) {
            return $this->noRoutePage();
        }
        return parent::load($id, $field);
    }

    /**
     * Load No-Route Page
     *
     * @return \Nagarro\CustomBlog\Model\Page
     */
    public function noRoutePage()
    {
        return $this->load(self::NOROUTE_PAGE_ID, $this->getIdFieldName());
    }
 
    /**
     * Get identities
     *
     * @return array
     */
    public function getIdentities()
    {
        return [self::CACHE_TAG . '_' . $this->getId()];
    }

    /**
     * Get ID
     *
     * @return int
     */
    public function getId()
    {
        return parent::getData(self::BLOG_ID);
    }
     /**
     * Check if page identifier exist for specific store return page id if page exists
     *
     * @param string $identifier
     * @param int $storeId
     * @return int
     */
    public function checkIdentifier($identifier, $storeId)
    {
        return $this->_getResource()->checkIdentifier($identifier, $storeId);
    }
}
