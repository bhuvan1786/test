<?php
namespace Custom\Blog\Controller\Index;

class Index extends \Magento\Framework\App\Action\Action
{
	protected $_pageFactory;
    protected $blogFactory;
    protected $example;
	protected $_coreRegistry;
    private $eventManager;

	public function __construct(
		\Magento\Framework\App\Action\Context $context,
		\Magento\Framework\View\Result\PageFactory $pageFactory,
        \Custom\Blog\Model\BlogFactory $blogFactory,
		\Magento\Framework\Registry $_coreRegistry
        )
	{
		$this->_pageFactory = $pageFactory;
        $this->blogFactory = $blogFactory;
		$this->_coreRegistry = $_coreRegistry;
		return parent::__construct($context);
	}

	public function execute()
	{
        
        $blog = $this->blogFactory->create();
		$arrBlogData = $blog->getCollection()->getData();

	    $resultPage = $this->_pageFactory->create();
		$this->_coreRegistry->register('blog_data', $arrBlogData);
		return $resultPage;
	}
}
