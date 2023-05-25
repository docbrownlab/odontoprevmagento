<?php

namespace Odontoprev\Checkout\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Checkout\Model\Cart;
use phpDocumentor\Reflection\DocBlock\Tags\Var_;

class PaymentMethodAvailable implements ObserverInterface {
    
    
    protected $objectManager;
    
    protected $cart;
    
    public function __construct() {
        $this->objectManager = \Magento\Framework\App\ObjectManager::getInstance();
    }

    /**
     * payment_method_is_active event handler.
     *
     * @param \Magento\Framework\Event\Observer $observer
     */
    public function execute(\Magento\Framework\Event\Observer $observer) {
        
        $cart = $observer->getEvent()->getQuote();

        $method = $cart->getPayment()->getMethod();

        $item = $cart->getItemsCollection(false)->getFirstItem();

        $sku = $item->getData("sku");

        $productFactory = $this->objectManager->create('\Magento\Catalog\Model\ProductFactory')->create();

        $product = $productFactory->load($productFactory->getIdBySku($sku));

        $plano = $product->getCustomAttribute('odontoprev_negotiation')->getValue();
    }

}
