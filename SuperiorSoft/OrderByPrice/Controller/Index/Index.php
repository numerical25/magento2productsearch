<?php
/**
 * Created by PhpStorm.
 * User: anthonygordon
 * Date: 5/8/18
 * Time: 4:48 PM
 */


namespace SuperiorSoft\OrderByPrice\Controller\Index;

class Index extends \Magento\Framework\App\Action\Action
{
    protected $_pageFactory;

    protected $_customerSession;

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $pageFactory,
        \Magento\Customer\Model\Session $customerSession
    )
    {
        $this->_pageFactory = $pageFactory;
        $this->_customerSession = $customerSession;
        return parent::__construct($context);
    }

    public function execute()
    {
        if (!($customerId = $this->_customerSession->getCustomerId())) {
            $this->_redirect('customer/account/login/');
            return;
        }
        return $this->_pageFactory->create();
    }
}