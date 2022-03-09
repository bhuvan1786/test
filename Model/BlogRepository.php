<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Custom\Blog\Model;

use Custom\Blog\Api\Data;
use Custom\Blog\Api\Data\BlogInterface;
use Custom\Blog\Api\Data\BlogInterfaceFactory;
//use Magento\Cms\Api\Data\PageSearchResultsInterface;
//use Magento\Cms\Api\PageRepositoryInterface;
use Custom\Blog\Api\BlogRepositoryInterface;
//use Magento\Cms\Model\Api\SearchCriteria\PageCollectionProcessor;
use Custom\Blog\Model\Blog\IdentityMap;
//use Magento\Cms\Model\ResourceModel\Page as ResourcePage;
use Custom\Blog\Model\ResourceModel\Blog as ResourceBlog;
//use Magento\Cms\Model\ResourceModel\Page\CollectionFactory as PageCollectionFactory;
use Custom\Blog\Model\ResourceModel\Blog\CollectionFactory as BlogCollectionFactory;
use Magento\Framework\Api\DataObjectHelper;
//use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\EntityManager\HydratorInterface;
use Magento\Framework\App\Route\Config;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Reflection\DataObjectProcessor;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Cms page repository
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class BlogRepository implements BlogRepositoryInterface
{
    /**
     * @var ResourceBlog
     */
    protected $resource;

    /**
     * @var BlogFactory
     */
    protected $blogFactory;

    /**
     * @var BlogCollectionFactory
     */
    protected $blogCollectionFactory;

    /**
     * @var Data\PageSearchResultsInterfaceFactory
     */
    //protected $searchResultsFactory;

    /**
     * @var DataObjectHelper
     */
    protected $dataObjectHelper;

    /**
     * @var DataObjectProcessor
     */
    protected $dataObjectProcessor;

    /**
     * @var BlogInterfaceFactory
     */
    protected $dataBlogFactory;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var CollectionProcessorInterface
     */
   // private $collectionProcessor;

    /**
     * @var IdentityMap
     */
    private $identityMap;

    /**
     * @var HydratorInterface
     */
    private $hydrator;

    /**
     * @var Config
     */
    private $routeConfig;

    /**
     * @param ResourceBlog $resource
     * @param BlogFactory $blogFactory
     * @param BlogInterfaceFactory $dataBlogFactory
     * @param BlogCollectionFactory $blogCollectionFactory
     * @param Data\PageSearchResultsInterfaceFactory $searchResultsFactory
     * @param DataObjectHelper $dataObjectHelper
     * @param DataObjectProcessor $dataObjectProcessor
     * @param StoreManagerInterface $storeManager
     * @param CollectionProcessorInterface $collectionProcessor
     * @param IdentityMap|null $identityMap
     * @param HydratorInterface|null $hydrator
     * @param Config|null $routeConfig
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        ResourceBlog $resource,
        BlogFactory $blogFactory,
        BlogInterfaceFactory $dataBlogFactory,
        BlogCollectionFactory $blogCollectionFactory,
      //  Data\PageSearchResultsInterfaceFactory $searchResultsFactory,
        DataObjectHelper $dataObjectHelper,
        DataObjectProcessor $dataObjectProcessor,
        StoreManagerInterface $storeManager,
        //CollectionProcessorInterface $collectionProcessor = null,
        ?IdentityMap $identityMap = null,
        ?HydratorInterface $hydrator = null,
        ?Config $routeConfig = null
    ) {
        $this->resource = $resource;
        $this->blogFactory = $blogFactory;
        $this->blogCollectionFactory = $blogCollectionFactory;
      //  $this->searchResultsFactory = $searchResultsFactory;
        $this->dataObjectHelper = $dataObjectHelper;
        $this->dataBlogFactory = $dataBlogFactory;
        $this->dataObjectProcessor = $dataObjectProcessor;
        $this->storeManager = $storeManager;
       // $this->collectionProcessor = $collectionProcessor ?: $this->getCollectionProcessor();
        $this->identityMap = $identityMap ?? ObjectManager::getInstance()
                ->get(IdentityMap::class);
        $this->hydrator = $hydrator ?: ObjectManager::getInstance()
            ->get(HydratorInterface::class);
        $this->routeConfig = $routeConfig ?? ObjectManager::getInstance()
                ->get(Config::class);
    }

   
    /**
     * Save Page data
     *
     * @param BlogInterface|Page $blog
     * @return Page
     * @throws CouldNotSaveException
     */
    public function save(BlogInterface $blog)
    {

        try {
            $blogId = $blog->getId();

            if ($blogId && !($blog instanceof Blog && $blog->getOrigData())) {
                $blog = $this->hydrator->hydrate($this->getById($blogId), $this->hydrator->extract($blog));
            }

            if ($blog->getStoreId() === null) {
                $storeId = $this->storeManager->getStore()->getId();
                $blog->setStoreId($storeId);
            }

            if( empty($blog->getData('blog_id'))){
                $txtIdentifier =  str_replace(" ","-", strtolower($blog->getData('title')));
                $blog->setData('identifier', $txtIdentifier);
            }
        //    $this->validateLayoutUpdate($blog); to do
          //  $this->validateRoutesDuplication($blog); to do

            $this->resource->save($blog);

            $this->identityMap->add($blog);

        } catch (LocalizedException $exception) {
            throw new CouldNotSaveException(
                __('Could not save the blog: %1', $exception->getMessage()),
                $exception
            );
        } catch (\Throwable $exception) {
            throw new CouldNotSaveException(
                __('Could not save the blog: %1', __('Something went wrong while saving the blog.')),
                $exception
            );
        }
        return $blog;
    }

  /**
     * Load Page data by given Page Identity
     *
     * @param string $blogId
     * @return Blog
     * @throws NoSuchEntityException
     */
    public function getById($blogId)
    {
        $blog = $this->blogFactory->create();
        $blog->load($blogId);
        if (!$blog->getId()) {
            throw new NoSuchEntityException(__('The CMS page with the "%1" ID doesn\'t exist.', $blogId));
        }
        $this->identityMap->add($blog);

        return $blog;
    }
}
