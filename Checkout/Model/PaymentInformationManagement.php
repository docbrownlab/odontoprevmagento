<?php

/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Odontoprev\Checkout\Model;

use Magento\Catalog\Model\ProductFactory;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Model\CustomerFactory;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Quote\Api\{
    BillingAddressManagementInterface,
    CartManagementInterface,
    CartRepositoryInterface,
    PaymentMethodManagementInterface
};
use Magento\Quote\Api\Data\{
    PaymentInterface,
    AddressInterface
};
use Magento\Quote\Model\QuoteManagement;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\Service\OrderService;
use Magento\Store\Model\{
    ScopeInterface,
    StoreManagerInterface
};
use Odontoprev\Agreement\Model\AgreementFactory;
use Odontoprev\Checkout\Api\PaymentInformationManagementInterface;
use Odontoprev\PlanLife\Model\{
    OrderItemLifeFactory,
    PlanLifeFactory
};
use Odontoprev\Quote\Model\FinancialResponsibleFactory as QuoteFinancialResponsibleFactory;
use Odontoprev\Sales\Helper\{
    GenerateProposal,
    Payment as PaymentHelper
};
use Odontoprev\Sales\Model\{
    FinancialResponsibleFactory as OrderFinancialResponsibleFactory,
    OrderDataFactory,
    OrderExtraFactory
};

use Odontoprev\BraspagIntegration\Model\PaymentAccountDebitFactory;
use Odontoprev\BraspagIntegration\Helper\BraspagIntegrationHelper;
use Odontoprev\Tags\Model\BrokerRepository;
use Psr\Log\LoggerInterface;
use Odontoprev\Tags\Model\OrderDataGridFactory;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use DateTime;

use Magento\Checkout\Api\PaymentInformationManagementInterface as PaymentInformationManagementInterfaceOrigin;
use Odontoprev\Checkout\Helper\CheckoutHelper;

use \Magento\Payment\Model\Config;

use \Magento\Sales\Model\ResourceModel\Order\CollectionFactory as OrderCollectionFactory;
use \Magento\Customer\Model\SessionFactory as SessionFactory;
use \Magento\Sales\Model\ResourceModel\Order\Address\Collection as OrderAddressCollection;
use Odontoprev\Checkout\Logger\Logger;

class PaymentInformationManagement implements PaymentInformationManagementInterface
{

    /**
     * @var \Magento\Catalog\Model\ProductFactory
     */
    protected $productFactory;

    /**
     * @var \Magento\Quote\Api\PaymentMethodManagementInterface
     */
    protected $paymentMethodManagement;

    /**
     * @var \Magento\Quote\Api\CartManagementInterface
     */
    protected $cartManagement;

    protected $main_quote_item_id;

    /**
     * @var \Odontoprev\Tags\Model\BrokerRepository
     */
    protected $brokerRepository;

    /**
     * @var \Odontoprev\Tags\Model\OrderDataGridFactory
     */
    protected $orderDataGridFactory;

    /**
     * @var \Odontoprev\Tags\Model\OrderDataGrid[]
     */
    protected $orderDataGrid = [];

    /**
     * @var \Magento\Framework\Stdlib\DateTime\TimezoneInterface
     */
    protected $timezone;

    /**
     * @var CustomerFactory
     */
    protected $customerFactory;

    /**
     * @var PaymentAccountDebitFactory
     */
    protected $paymentAccountDebitFactory;

    /**
     * @var BraspagIntegrationHelper
     */
    protected $braspagIntegrationHelper;

    /**
     * @var PaymentInformationManagementInterfaceOrigin
     */
    protected $paymentInformationManagementOrigin;

    protected $activePayments;

    protected $orderCollectionFactory;

    protected $orderAddressCollection;

    protected $customLogger;
    
    /**
     * @param \Magento\Quote\Api\PaymentMethodManagementInterface $paymentMethodManagement
     * @param \Magento\Quote\Api\CartManagementInterface $cartManagement
     * @codeCoverageIgnore
     */
    public function __construct(
        ProductFactory $productFactory,
        CustomerRepositoryInterface $customerRepository,
        CustomerFactory $customerFactory,
        ScopeConfigInterface $scopeConfig,
        BillingAddressManagementInterface $billingAddressManagement,
        CartManagementInterface $cartManagementInterface,
        CartRepositoryInterface $cartRepositoryInterface,
        PaymentMethodManagementInterface $paymentMethodManagementInterface,
        QuoteManagement $quoteManagement,
        OrderRepositoryInterface $orderRepositoryInterface,
        OrderService $orderService,
        StoreManagerInterface $storeManager,
        AgreementFactory $agreementFactory,
        OrderItemLifeFactory $orderItemLifeFactory,
        PlanLifeFactory $planLifeFactory,
        QuoteFinancialResponsibleFactory $quoteFinancialResponsibleFactory,
        GenerateProposal $generateProposal,
        PaymentHelper $paymentHelper,
        OrderFinancialResponsibleFactory $orderFinancialResponsibleFactory,
        OrderDataFactory $orderDataFactory,
        OrderExtraFactory $orderExtraFactory,
        LoggerInterface $logger,
        BrokerRepository $brokerRepository,
        OrderDataGridFactory $orderDataGridFactory,
        TimezoneInterface $timezone,
        PaymentAccountDebitFactory $paymentAccountDebitFactory,
        BraspagIntegrationHelper $braspagIntegrationHelper,
        PaymentInformationManagementInterfaceOrigin $paymentInformationManagementOrigin,
        CheckoutHelper $checkoutHelper,
        Config $activePayments,
        OrderCollectionFactory $orderCollectionFactory,
        OrderAddressCollection $orderAddressCollection,
        Logger $customLogger
    ) {
        $this->productFactory = $productFactory;
        $this->scopeConfig = $scopeConfig;
        $this->billingAddressManagement = $billingAddressManagement;
        $this->cartManagement = $cartManagementInterface;
        $this->cartRepository = $cartRepositoryInterface;
        $this->paymentMethodManagement = $paymentMethodManagementInterface;
        $this->orderRepository = $orderRepositoryInterface;
        $this->agreementFactory = $agreementFactory;
        $this->orderItemLifeFactory = $orderItemLifeFactory;
        $this->planLifeFactory = $planLifeFactory;
        $this->quoteFinancialResponsibleFactory = $quoteFinancialResponsibleFactory;
        $this->generateProposal = $generateProposal;
        $this->paymentHelper = $paymentHelper;
        $this->orderFinancialResponsibleFactory = $orderFinancialResponsibleFactory;
        $this->orderDataFactory = $orderDataFactory;
        $this->orderExtraFactory = $orderExtraFactory;
        $this->logger = $logger;
        $this->brokerRepository = $brokerRepository;
        $this->orderDataGridFactory = $orderDataGridFactory;
        $this->timezone = $timezone;
        $this->customerFactory = $customerFactory;
        $this->paymentAccountDebitFactory = $paymentAccountDebitFactory;
        $this->braspagIntegrationHelper = $braspagIntegrationHelper;
        $this->activePayments = $activePayments;
        $this->paymentInformationManagementOrigin = $paymentInformationManagementOrigin;
        $this->checkoutHelper = $checkoutHelper;
        $this->objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $this->orderCollectionFactory = $orderCollectionFactory;
        $this->orderAddressCollection = $orderAddressCollection;
        $this->customLogger = $customLogger;
        $this->_storeManager = $storeManager;
    }

    /**
     * {@inheritDoc}
     */
    public function savePaymentInformationAndPlaceOrder(
        $cartId,
        PaymentInterface $paymentMethod,
        AddressInterface $billingAddress = NULL,
        $brokerCode = NULL,
        $couponCode = NULL,
        $processingQueue = NULL,
        $accountDebit = NULL,
        $retailerId = NULL
    )
    {
        $this->billingAddress = $billingAddress;
 
        try {

            if (! is_array($processingQueue)) {
                 $response = $this->multiplePlaceOrder(
                    $cartId, 
                    $paymentMethod, 
                    $brokerCode, 
                    $couponCode, 
                    $accountDebit,
                    $retailerId
                );
            } else {
                $response = $this->reprocessOrders($processingQueue);
            }
            
            $this->customLogger->error('Pedido concluído ');
            $this->customLogger->error('Response: '.\GuzzleHttp\json_encode($response));

            return [[
                'success' => 1,
                'data' => $response
            ]];
        } catch (\Exception $e) {
            $this->customLogger->error('ERRO NO PROCESSAMENTO: '.$e->getMessage());
            return [[
                'success' => 0,
                'message' => $e->getMessage()
            ]];
        }
    }

    /**
     * {@inheritDoc}
     */
    public function savePaymentInformation(
        $cartId,
        PaymentInterface $paymentMethod,
        AddressInterface $address = null
    )
    {
        // if ($address) {
        //     $this->billingAddressManagement->assign($cartId, $address);
        // }
        $this->paymentMethodManagement->set($cartId, $paymentMethod);
        return true;
    }

    public function handleGenerateProposal($order_id, $quoteItem, $brokerCode, $couponCode, $holder, $retailerId)
    {

        $order = $this->orderRepository->get($order_id);
        $storeId = $this->paymentHelper->getStoreId($quoteItem->getProduct());

        $initials = $this->scopeConfig->getValue(
            'general_settings/company_configuration/initials',
            ScopeInterface::SCOPE_STORE,
            $storeId
        );

        $customer = $this->customerFactory->create()->loadByEmail($order->getCustomerEmail());
        $phone = $customer->getDefaultBillingAddress()->getTelephone();

        $proposal_number = $this->generateProposal->execute($initials, $order->getIncrementId());

        // Salva ou atualiza as informações da proposta do pedido
        $orderData = $this->orderDataFactory->create()
            ->getCollection()
            ->addFieldToFilter('order_id', ['eq' => $order_id])
            ->getFirstItem();

        $orderData->setOrderId($order_id);
        $orderData->setProposalNumber($proposal_number);

        if ($brokerCode) {
            $orderData->setBrokerId($this->getBrokerId($brokerCode));            
        }

        if ($couponCode) {
            $orderData->setCouponCode($couponCode);            
        }


        if ($retailerId){
            $orderData->setRetailerId($retailerId);
        }
//   
        // Cria logs para serem salvos
        $dados = array_merge(array('order_id' => $order_id),
                            array('brokerCode' => $brokerCode), 
                            array('couponCode' => $couponCode), 
                            array('holder' => $holder),
                            array('proposal_number' => $proposal_number),
                            // array('order' => $order),
                            array('storeId' => $storeId),
                            array('initials' => $initials),
                            // array('order item' => $order->getItems()[0]),
                            array('sku' => array_values($order->getItems())[0]->getSku()),
                            array('modality' => $this->getProductModality(array_values($order->getItems())[0]->getProductId()))
                            
                        );

        // registrando o log no banco de dados
        $logSaved = $this->saveLogSale($order_id, $dados);
        
        $this->customLogger->error('Pedido gerado');
        $this->customLogger->error('order_id: '.$order_id);
        $this->customLogger->error('proposal_number: '.$proposal_number);
        
        $holder = array_splice($holder, 0, 1);
        

        $this->updateDataGrid($order_id, [
            'broker_code' => $brokerCode ?: null,
            'coupon_code' => $couponCode ?: null,
            'created_at_fields' => $order->getCreatedAt(),
            'sku' => array_values($order->getItems())[0]->getSku(),
            'cpf' => $holder[0]['cpf'],
            'modality' => $this->getProductModality(array_values($order->getItems())[0]->getProductId()),
            'phone' => $phone
        ]);

        $orderData->save();
    }


    function saveLogSale($order_id, $dados = null){
        // save dados in database
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $resource = $objectManager->get('Magento\Framework\App\ResourceConnection');
        $connection = $resource->getConnection();

        //Insert Data into table
        $sql = "Insert Into odontoprev_log_details_propostal Values ('".$order_id."', '".json_encode($dados)."',now())";
        $connection->query($sql);

        return $dados;
    }

    /**
     * Builds Modality string from product
     * 
     * @param int $productId
     * 
     * @return string
     */
    private function getProductModality($productId)
    {
        $factory = $this->productFactory->create();
        $product = $factory->load($productId);
        $negotiation = ucfirst($product->getOdontoprevNegotiation());
        $hasGrace = $product->getOdontoprevGracePeriod() == 0 ? 'sem' : 'com';
        return "{$negotiation} {$hasGrace} Carência";
    }

    /**
     * Update a order grid item
     * 
     * @param int $orderId
     * @param mixed[] $data
     */
    private function updateDataGrid($orderId, $data)
    {
        foreach ($data as $field => $value) {
            if (!is_null($value)) {
                $this->updateDataGridField($orderId, $field, $value);
            }
        }
    }

    /**
     * Update sales_order_grid
     * 
     * @param int    $orderId
     * @param string $field
     * @param string $value 
     */
    private function updateDataGridField($orderId, $field, $value)
    {
        if (!isset($this->orderDataGrid[$orderId])) {
            $orderDataGridFactory = $this->orderDataGridFactory->create();
            $this->orderDataGrid[$orderId] = $orderDataGridFactory->load($orderId, 'entity_id');
        }

        switch ($field) {
            case 'broker_code':
                $this->orderDataGrid[$orderId]->setBrokerCode($value);
                break;
            case 'coupon_code':
                $this->orderDataGrid[$orderId]->setCouponCode($value);
                break;
            case 'created_at_fields':
                $datetime = $this->timezone->date(new DateTime($value));
                $this->orderDataGrid[$orderId]->setCreatedAtDate($datetime->format('Y-m-d'));
                $this->orderDataGrid[$orderId]->setCreatedAtTime($datetime->format('H:i:s'));
                break;
            case 'sku':
                $this->orderDataGrid[$orderId]->setSku($value);
                break;
            case 'cpf':
                $this->orderDataGrid[$orderId]->setCpf($value);
                break;
            case 'life_count':
                $this->orderDataGrid[$orderId]->setLifeCount($value);
                break;
            case 'modality':
                $this->orderDataGrid[$orderId]->setModality($value);
                break;
            case 'phone':
                $this->orderDataGrid[$orderId]->setPhone($value);
                break;
            case 'number_installment':
                $this->orderDataGrid[$orderId]->setNumberInstallment($value);
                break;
        }

        $this->orderDataGrid[$orderId]->save();
    }

    /**
     * {@inheritDoc}
     */
    public function multiplePlaceOrder(
        $cartId, 
        $paymentMethod, 
        $brokerCode, 
        $couponCode, 
        $accountDebit,
        $retailerId
    ) {

        // Carrinho ativo
        $activeCart = $this->cartRepository->getActive($cartId);

        if (!$this->checkAttempts($activeCart)){
            throw new \Exception("Limit attempts");
        }

        $order_ids['parent'] = NULL;
        $order_ids['children'] = [];

        $this->responsible = $this->getQuoteFinancialResponsible($cartId);

        if (!count($this->responsible)) {
            throw new \Exception("Financial Responsible not informed");
        }

        // Items no carrinho ativo
        $quoteItems = $activeCart->getAllItems();

        if (!count($quoteItems)) {
            throw new \Exception('The cart is empty');
        }

        // Obtem os ids dos items do carrinho
        $itemIds = array_map(function($quoteItem) {
            return $quoteItem->getId();
        }, $quoteItems);

        // Obtem todas as vidas salvas para os planos(itens) no carrinho
        $lives = $this->planLifeFactory->create()
            ->getCollection()
            ->addFieldToFilter('quote_item_id', ['in' => $itemIds]);

        // Vidas agrupadas pelo plano(item)
        $livesMapByQuoteItemId = [];
        $has_main = false;
        foreach ($lives as $life) {
            $quote_item_id = $life->getData('quote_item_id');
            $livesMapByQuoteItemId[$quote_item_id][] = $life->getData();
            if ($life->getData('is_main')) {
                $has_main = true;
                $this->main_quote_item_id = $quote_item_id;
            }
        }

        if (!$has_main) {
            throw new \Exception('Not has main life');
        }

        /**
         * Se tivermos mais de um item no carrinho, vamos gerar mais de um pedido (vai ser um pedido por item no carrinho)
         */
        if (count($quoteItems) > 1) {
            /**
             *  Filtra os itens do carrinho removendo o item principal(que contém a vida principal), que será deixado no carrinho ativo e será gerado um pedido a partir desse carrinho, que será o pai dos demais pedidos gerados
             */
            $extraQuoteItems = array_filter($quoteItems, function($quoteItem) {
                $quote_item_id = $quoteItem->getId();
                return $this->main_quote_item_id !== $quote_item_id;
            });

            // Gerando pedidos adicionais e vinculando ao carrinho pai.
            foreach ($extraQuoteItems as $quoteItem) {
                // ID do item no carrinho
                $quote_item_id = $quoteItem->getId();

                // Vidas que foram adicionadas no item do carrinho (plano)
                $lives = $livesMapByQuoteItemId[$quote_item_id];

                // Cria um novo pedido para o plano
                $extra_data = $this->extraOrder($activeCart, $paymentMethod, clone $quoteItem, $lives);

                $order_ids['children'][] = [
                    'orderId' => $extra_data['order_id'],
                    'sku' => $extra_data['sku']
                ];

                $holder = array_filter($lives, function($live) {
                    return $live['is_holder'];
                });

                // Gerando informações da proposta
                $this->handleGenerateProposal($extra_data['order_id'], $quoteItem, $brokerCode, $couponCode, $holder, $retailerId);

                // Mapeamento do carrinho que gerou o  pedido extra.
                $orderExtra = $this->orderExtraFactory->create();
                $orderExtra->setData([
                    'order_id' => $extra_data['order_id'],
                    'parent_quote_id' => $activeCart->getId()
                ]);
                $orderExtra->save();

                // Salva responsável financeiro do pedido
                $this->saveOrderFinancialResponsible($extra_data['order_id']);

                // Salva a conta bancaria no banco de dados
                $this->savePaymentAccountDebit(
                    $extra_data['order_id'], 
                    $accountDebit
                );

                // Insere aceite para cada carrinho criado
                $this->agreementSave($cartId, $extra_data['cart_id']);

                $this->updateDataGrid($extra_data['order_id'], [
                    'life_count' => count($lives)
                ]);

                // Remove o item do carrinho ativo
                $activeCart->removeItem($quote_item_id);
                $activeCart->save();
            }
        }
        $this->customLogger->error('Inicio transação... ');
        $this->customLogger->error('Processando pagamento e pedido de ' . $activeCart->getCustomerEmail());
        $this->customLogger->error('Forma de pagamento: '.$paymentMethod->getMethod());
        $this->customLogger->error('Loja: '.$this->_storeManager->getStore()->getStoreId());
 
        $cart_id = $activeCart->getId();
        $this->savePaymentInformation($cart_id, $paymentMethod, $this->billingAddress);

        // Vidas que foram adicionadas no item do carrinho (plano)
        $quoteItem = $this->getFirstItemCart($activeCart);
        $quote_item_id = $quoteItem->getId();
        $lives = $livesMapByQuoteItemId[$quote_item_id];

        // Gera o pedido principal
        $order_id = $this->cartManagement->placeOrder($cart_id);
        $order_ids['parent'] = [
            'orderId' => $order_id,
            'sku' => $quoteItem->getProduct()->getSku()
        ];

        $order = $this->orderRepository->get($order_id);

        $holder = array_filter($lives, function($live) {
            return $live['is_holder'];
        });

        // Gerando informações da proposta
        $this->handleGenerateProposal($order_id, $quoteItem, $brokerCode, $couponCode, $holder, $retailerId);

        $this->updateDataGrid($order_id, [
            'life_count' => count($lives)
        ]);

        // Salva responsável financeiro do pedido
        $this->saveOrderFinancialResponsible($order_id);

        // Salva as vidas do pedido principal
        $this->saveQuoteItemLifeForOrderItem($order, $lives);

        // Atualiza a tabela de mapeamento de pedido extra com o id do pedido pai
        $this->setParentOrderIdFromOrderExtra($cart_id, $order_id);

        // Salva a conta bancaria no banco de dados
        $this->savePaymentAccountDebit($order_id, $accountDebit);

        return $order_ids;
    }

    public function reprocessOrders(array $processingQueue)
    {
        $order_ids = [
            'parent' => [],
            'children' => []
        ];

        $order_ids['parent'] = array_pop($processingQueue);

        $orderObj = $this->orderRepository->get($order_ids['parent']['orderId']);

        $orderObj->getPayment()->authorize(true, $orderObj->getBaseGrandTotal());
        $orderObj->save();

        foreach ($processingQueue as $order) {
            $order_ids['children'][] = $order;
            $orderObj = $this->orderRepository->get($order['orderId']);
            $orderObj->getPayment()->authorize(true, $orderObj->getBaseGrandTotal());
            $orderObj->save();
        }

        return $order_ids;
    }

    /**
     * {@inheritDoc}
     */
    public function getQuoteFinancialResponsible($cartId)
    {
        return $this->quoteFinancialResponsibleFactory
            ->create()
            ->getCollection()
            ->addFieldToFilter('quote_id', ['eq' => $cartId])
            ->getFirstItem()
            ->getData();
    }

    /**
     * {@inheritDoc}
     */
    public function saveOrderFinancialResponsible($currentCartId)
    {
        return $this->orderFinancialResponsibleFactory->create()
            ->setData([
                'order_id' => $currentCartId,
                'full_name' => $this->responsible['full_name'],
                'birth_date' => $this->responsible['birth_date'],
                'cpf' => $this->responsible['cpf'],
                'is_main' => $this->responsible['is_main']
            ])
            ->save();
    }

    /**
     * {@inheritDoc}
     */
    public function agreementSave($mainCartId, $currentCartId)
    {
        $data = $this->agreementFactory->create()
           ->getCollection()
           ->addFieldToFilter(
                'quote_id',
                ['eq' => (int) $mainCartId]
           )
           ->getData();
        $agreement = current($data);

        if (!empty($agreement)) {
            $newAgreement = $this->agreementFactory->create();

            $newAgreement->setData([
                'quote_id' => $currentCartId,
                'ip' => $agreement['ip'],
                'datetime' => $agreement['datetime']
            ]);

            $newAgreement->save();
        }
    }

    /**
     * {@inheritDoc}
     */
    public function extraOrder($activeCart, $paymentMethod, $quoteItem, $lives = [])
    {
        $store = $activeCart->getStore();
        $currency = $activeCart->getCurrency();
        $customer = $activeCart->getCustomer();
        $shippingAddress = $activeCart->getShippingAddress();
        $billingAddress = $activeCart->getBillingAddress();

        // Criar um novo carrinho
        $cart_id = $this->cartManagement->createEmptyCart();
        $cart = $this->cartRepository->get($cart_id);

        /** Configura o novo carrinho com os mesmos dados do ativo
         */
        $cart->setIsActive(false); // Só vamos deixar um carrinho como ativo para o usuário.
        $cart->setStore($store);
        $cart->setCurrency($currency);
        $cart->assignCustomer($customer);

        $shippingAddress->setAddressId(null);
        $shippingAddress->setQuoteId($cart_id);
        $cart->setShippingAddress($shippingAddress);

        $billingAddress->setAddressId(null);
        $billingAddress->setQuoteId($cart_id);
        $cart->setBillingAddress($billingAddress);

        /** Move o item do carrinho informado para o novo carrinho criado
         */
        // $quoteItem->setQuote($cart);
        // $quoteItem->save();

        $quoteItem->setItemId(null);
        $cart->addItem($quoteItem);

        // $cart->collectTotals();

        $cart->save();

        // $cart = $this->cartRepository->get($cart->getId());

        // Obtendo o ID do item adicionado
        $quoteItem = $this->getFirstItemCart($cart);
        $quote_item_id = $quoteItem->getId();

        $new_lives = [];
        // Salvando as vidas que foram adicionadas ao item (plano)
        foreach ($lives as $life) {
            $life['life_id'] = null;
            $life['quote_item_id'] = $quote_item_id;
            $planLife = $this->planLifeFactory->create();
            $planLife->setData($life);
            $planLife->save();
            $new_lives[] = $planLife->getData();
        }

        $this->savePaymentInformation($cart->getId(), $paymentMethod, $this->billingAddress);

        $order = $this->cartManagement->submit($cart);

        $this->saveQuoteItemLifeForOrderItem($order, $new_lives);

        return [
            'order_id' => $order->getId(),
            'cart_id' => $cart->getId(),
            'sku' => $quoteItem->getProduct()->getSku()
        ];
    }

    /**
     * {@inheritDoc}
     */
    public function getFirstItemCart($cart)
    {
        return current($cart->getAllItems());
    }

    /**
     * {@inheritDoc}
     */
    public function getFirstItemOrder($order)
    {
        return current($order->getItems());
    }

    /**
     * {@inheritDoc}
     */
    public function saveQuoteItemLifeForOrderItem($order, $lives = array())
    {

        // Obtem o item (plano) do pedido gerado
        $orderItem = $this->getFirstItemOrder($order);

        // Vincula as vidas ao item de pedido
        foreach ($lives as $life) {
            $life['life_id'] = null;
            $life['order_item_id'] = $orderItem->getId();
            $orderItemLife = $this->orderItemLifeFactory->create();
            $orderItemLife->setData($life);
            $orderItemLife->save();
        }
    }

    /**
     * {@inheritDoc}
     */
    public function setParentOrderIdFromOrderExtra($parent_quote_id, $parent_order_id)
    {

        $collections = $this->orderExtraFactory
            ->create()
            ->getCollection()
            ->addFieldToFilter('parent_quote_id', ['eq' => $parent_quote_id]);
        foreach($collections as $item) {
            $item->setParentOrderId($parent_order_id);
        }
        $collections->save();
    }

    /**
     * {@inheritDoc}
     */
    private function getBrokerId($brokerCode)
    {
        $broker = $this->brokerRepository->getByCode($brokerCode);

        return $broker['odontoprev_tags_broker_id'] ?? NULL;
    }

    private function savePaymentAccountDebit(
        $orderId,
        $dataAccount
    ){

        if(is_null($dataAccount) || !is_array($dataAccount)){
            return NULL;
        }

        $agencyDv = NULL;

        if ($dataAccount['bankCode'] == '237') {
            $agencyDv = $this->braspagIntegrationHelper->calculateAgencyCheckDigit(
                $dataAccount['agency']
            );
        }

        $data = [
            'order_id'      => $orderId,
            'bank_code'     => $dataAccount['bankCode'],
            'account'       => $dataAccount['account'],
            'account_dv'    => $dataAccount['accountDv'],
            'agency'        => $dataAccount['agency'],
            'agency_dv'     => $agencyDv
        ];

        return $this->paymentAccountDebitFactory->create()->setData($data)->save();
    }

    public function getPaymentInformation($cartId)
    {
        $paymentDetails = $this->paymentInformationManagementOrigin->getPaymentInformation($cartId);
        $paymentDetailsCustom = $this->objectManager->create('Odontoprev\Checkout\Model\PaymentDetailsCustom');
        $installmentOptions = $this->checkoutHelper->getListInstallmentOptions($cartId,
            $paymentDetails
                ->getTotals()
                ->getGrandTotal()
        );

        $paymentDetailsCustom->setInstallmentOptions($installmentOptions);
        $paymentDetailsCustom->setTotals($paymentDetails->getTotals());
        $paymentDetailsCustom->setPaymentMethods($this->getActivePayments());
        $paymentDetailsCustom->setFormPaymentInstallment($this->getFormPaymentInstallment());
        $paymentDetailsCustom->setBankList($this->getBankList());

        return $paymentDetailsCustom;
    }

    private function getActivePayments()
    {
        $methods = [];
        $payments = $this->activePayments->getActiveMethods();

        foreach ($payments as $paymentCode => $paymentModel) {
            $paymentTitle = $this->scopeConfig->getValue('payment/'.$paymentCode.'/title');
            $methods[$paymentCode] = array(
                'code' => $paymentCode,
                'title' => $paymentTitle
            );
        }

        array_splice($methods, 0, 1);

        return $methods;
    }

    private function getFormPaymentInstallment()
    {
        $installments = ['installment_three', 'installment_six', 'installment_twelve'];

        $formPaymentInstallment[0] = '1';

        foreach ($installments as $key => $installment) {
            $formPaymentInstallment[$key + 1] = $this->scopeConfig
                ->getValue('payment/mip_integration_creditcard/'.$installment,
                    \Magento\Store\Model\ScopeInterface::SCOPE_STORE
                );
        }

        return $formPaymentInstallment;
    }

    private function getBankList()
    {
        return $this->scopeConfig
            ->getValue('payment/braspag_integration_billet_account_debit/bank_list',
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE
            );
    }

    public function updateDataGridNumberInstallment($orderId, $value)
    {
        $this->updateDataGrid($orderId, ['number_installment' => $value]);
    }

    private function checkAttempts($activeCart)
    {
        $now = date("Y-m-d H:i:s");
        $limit = date('Y-m-d H:i:s',strtotime('-1 hour',strtotime($now)));

        $billingAddress = $activeCart->getBillingAddress();

        $postCode = $billingAddress->getPostcode();

        if (!$this->checkPostCodeAttempt($limit, $postCode)){
            return false;
        }

        $customer = $activeCart->getCustomer();

        $firstName = $customer->getFirstname();

        $lastName = $customer->getLastname();

        if (!$this->checkNameAttempt($limit, "customer_firstname", $firstName)
        && !$this->checkNameAttempt($limit, "customer_lastname", $lastName)
        ){
            return false;
        }

        return true;
    }

    private function checkNameAttempt($limit, $field, $value)
    {
        $orderCollection = $this->getOrderCollection($limit, $field, $value);

        $orderCollection = $orderCollection->addFieldToFilter($field, ["eq" => $value]);

        if ($orderCollection->count() > 10){
            return false;
        }

        return true;
    }

    private function checkPostCodeAttempt($limit, $postCode)
    {
        $orderCollection = $this->getOrderCollection($limit);

        $count = 0;

        foreach ($orderCollection as $order){
            $billingAddress = $order->getBillingAddress();

            if ($billingAddress->getPostcode() == $postCode){
                $count++;
            }

            if ($count > 10){
                return false;
            }
        }

        return true;

    }

    private function getOrderCollection($limit){
        $orderCollection = $this->orderCollectionFactory->create()
            ->addAttributeToSelect('*')
            ->addFieldToFilter("created_at", ["gteq" => $limit]);

        return $orderCollection;
    }
    
}
