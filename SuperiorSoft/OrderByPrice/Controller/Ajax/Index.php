<?php
/**
 * Created by PhpStorm.
 * User: anthonygordon
 * Date: 5/8/18
 * Time: 4:48 PM
 */


namespace SuperiorSoft\OrderByPrice\Controller\Ajax;

class Index extends \Magento\Framework\App\Action\Action
{
    protected $_pageFactory;

    protected $_orderCollectionFactory;

    protected $_customerSession;

    protected $_orderConfig;

    protected $_productCollectionFactory;

    protected $_stockItem;

    protected $_storeManager;

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $pageFactory,
        \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $orderCollectionFactory,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Sales\Model\Order\Config $orderConfig,
        \Magento\CatalogInventory\Model\Stock\Item $stockItem,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    )
    {
        $this->_pageFactory = $pageFactory;
        $this->_customerSession = $customerSession;
        $this->_orderCollectionFactory = $orderCollectionFactory;
        $this->_productCollectionFactory = $productCollectionFactory;
        $this->_stockItem = $stockItem;
        $this->_orderConfig = $orderConfig;
        $this->_storeManager = $storeManager;

        return parent::__construct($context);
    }

    public function execute()
    {
        $errorMessage = '';
        $productArray = [];
        $error = false;

        if (!($customerId = $this->_customerSession->getCustomerId())) {
            return false;
        }

        $params = $this->_request->getParams();
        if((!isset($params['lowPrice']) ||  !is_numeric($params['lowPrice'])) ||
            (!isset($params['highPrice']) || !is_numeric($params['highPrice'])) ||
            (!isset($params['sortPrice'])))  {
            $error = true;
            $errorMessage = 'Params must be set and numeric';
        }
        $ceiling = $params['lowPrice'] * 5;
        if($params['highPrice'] > $ceiling) {
            $error = true;
            $errorMessage = 'High Price Must be less then 5x the Low Price';
        }

        if(!$error) {
            $productArray = $this->getFilteredProductsByParams($params);
            if(empty($productArray)) {
                $errorMessage = 'There were no Products in this price range.';
            }
        }

        $result = ['items'=>$productArray,'errorMessage'=>$errorMessage];
        echo json_encode($result);
        exit;
    }

    public function getFilteredProductsByParams($params) {
        $productArray = [];
        $products = $this->_productCollectionFactory->create()->addAttributeToSelect(
            '*'
        )->addFieldToFilter(
            'price', array('gteq' => $params['lowPrice'])
        )->addFieldToFilter(
            'price', array('lteq' => $params['highPrice'])
        )->setOrder(
            'price',
            $params['sortPrice']
        )->setPageSize(10);

        foreach($products as $item =>$value) {
            $stockItem = $this->_stockItem->load($value->getId());
            $value->setQty($stockItem->getQty());
            $value->setFullImageUrl($this->getFullImageUrl($value));
            $value->setFullProductUrl($this->getProductFullUrl($value));
            $productArray[] = $value->getData();
        }
        return $productArray;
    }

    public function getFullImageUrl($product){

        return $this->_storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA) . 'catalog/product' . $product->getImage();
    }

    public function getProductFullUrl($product) {
        return $product->getProductUrl();
    }
}