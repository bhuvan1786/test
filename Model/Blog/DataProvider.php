<?php
// namespace Nagarro\CustomBlog\Model\Blog;
// use Magento\Ui\DataProvider\AbstractDataProvider;
// use Magento\Ui\DataProvider\Modifier\ModifierInterface;
// use Magento\Ui\DataProvider\Modifier\PoolInterface;
// use Nagarro\CustomBlog\Model\ResourceModel\Blog\CollectionFactory;
// class DataProvider extends AbstractDataProvider
// {
//     /**
//      * @var array
//      */
//     protected $_loadedData;
  
//     public function __construct(
//         $name,
//         $primaryFieldName,
//         $requestFieldName,
//         CollectionFactory $CollectionFactory,
//         array $meta = [],
//         array $data = []
//     ) {
    

//         $this->collection = $CollectionFactory->create();
//         parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
//     }
  
//     public function getData()
//     {
//         if (isset($this->_loadedData)) {
//             return $this->_loadedData;
//         }
//         $items = $this->collection->getItems();
//         foreach ($items as $brand) {
//             $this->_loadedData[$brand->getEntityId()] = $brand->getData();
//         }
//         return $this->_loadedData;
//     }
// }


namespace Custom\Blog\Model\Blog;


//use Magento\Cms\Api\Data\PageInterface;
use Custom\Blog\Api\Data\BlogInterface;

use Custom\Blog\Api\BlogRepositoryInterface;
//use Magento\Cms\Model\PageFactory;
use Custom\Blog\Model\BlogFactory;

use Custom\Blog\Model\ResourceModel\Blog\CollectionFactory;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\AuthorizationInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Ui\DataProvider\Modifier\PoolInterface;
use Magento\Ui\DataProvider\ModifierPoolDataProvider;
use Psr\Log\LoggerInterface;

/**
 * Cms Page DataProvider
 */
class DataProvider extends ModifierPoolDataProvider
{
    /**
     * @var DataPersistorInterface
     */
    protected $dataPersistor;

    /**
     * @var array
     */
    protected $loadedData;

    /**
     * @var BlogRepositoryInterface
     */
    private $blogRepository;

    /**
     * @var AuthorizationInterface
     */
    private $auth;

    /**
     * @var RequestInterface
     */
    private $request;

    /**
     * @var CustomLayoutManagerInterface
     */
  //  private $customLayoutManager;

    /**
     * @var BlogFactory
     */
    private $blogFactory;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param string $name
     * @param string $primaryFieldName
     * @param string $requestFieldName
     * @param CollectionFactory $blogCollectionFactory
     * @param DataPersistorInterface $dataPersistor
     * @param array $meta
     * @param array $data
     * @param PoolInterface|null $pool
     * @param AuthorizationInterface|null $auth
     * @param RequestInterface|null $request
     * @param CustomLayoutManagerInterface|null $customLayoutManager
     * @param BlogRepositoryInterface|null $blogRepository
     * @param BlogFactory|null $blogFactory
     * @param LoggerInterface|null $logger
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        CollectionFactory $blogCollectionFactory,
        DataPersistorInterface $dataPersistor,
        array $meta = [],
        array $data = [],
        PoolInterface $pool = null,
        ?AuthorizationInterface $auth = null,
        ?RequestInterface $request = null,
      //  ?CustomLayoutManagerInterface $customLayoutManager = null,
        ?BlogRepositoryInterface $blogRepository = null,
        ?BlogFactory $blogFactory = null,
        ?LoggerInterface $logger = null
    ) {
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data, $pool);
        $this->collection = $blogCollectionFactory->create();
        $this->dataPersistor = $dataPersistor;
        $this->auth = $auth ?? ObjectManager::getInstance()->get(AuthorizationInterface::class);
        $this->meta = $this->prepareMeta($this->meta);
        $this->request = $request ?? ObjectManager::getInstance()->get(RequestInterface::class);
   //     $this->customLayoutManager = $customLayoutManager
     //       ?? ObjectManager::getInstance()->get(CustomLayoutManagerInterface::class);
        $this->blogRepository = $blogRepository ?? ObjectManager::getInstance()->get(BlogRepositoryInterface::class);
        $this->blogFactory = $blogFactory ?: ObjectManager::getInstance()->get(BlogFactory::class);
        $this->logger = $logger ?: ObjectManager::getInstance()->get(LoggerInterface::class);
    }

    /**
     * Prepares Meta
     *
     * @param array $meta
     * @return array
     */
    public function prepareMeta(array $meta)
    {
        return $meta;
    }

    /**
     * Get data
     *
     * @return array
     */
    public function getData()
    {
        if (isset($this->loadedData)) {
            return $this->loadedData;
        }

        $blog = $this->getCurrentPage();
           $this->loadedData[$blog->getId()] = $blog->getData();

     //    if ($page->getCustomLayoutUpdateXml() || $page->getLayoutUpdateXml()) {
     //        //Deprecated layout update exists.
     //        $this->loadedData[$page->getId()]['layout_update_selected'] = '_existing_';
     //    }

        return $this->loadedData;
    }
 /**
     * Return current page
     *
     * @return BlogInterface
     */
    private function getCurrentPage(): BlogInterface
    {
        $blogId = $this->getBlogId();

        if ($blogId) {
            try {
                $blog = $this->blogRepository->getById($blogId);
            } catch (LocalizedException $exception) {
                $blog = $this->blogFactory->create();
            }

            return $blog;
        }

        $data = $this->dataPersistor->get('cms_blog');

           if (empty($data)) {
            return $this->blogFactory->create();
        }

        return $this->blogFactory->create()
            ->setData($data);
    }
      /**
     * Returns current page id from request
     *
     * @return int
     */
    private function getBlogId(): int
    {
        return (int) $this->request->getParam($this->getRequestFieldName());
    }
}
