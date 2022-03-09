<?php
namespace Custom\Blog\Block;
class Blogdata extends \Magento\Framework\View\Element\Template
{
    protected $_coreRegistry;

    public function __construct(
          \Magento\Framework\View\Element\Template\Context $context,
          \Magento\Framework\Registry $_coreRegistry
      ) {
          $this->_coreRegistry = $_coreRegistry;
          return parent::__construct($context);

      }
     public function getBlogData()
      {
          $postCollection = $this->_coreRegistry->registry('blog_data');
          return $postCollection;
      }
}