<?php

namespace Odontoprev\Checkout\Helper;

use Magento\Framework\App\Helper\AbstractHelper;

use Magento\Quote\Api\CartRepositoryInterface;
use Odontoprev\MipIntegration\Api\CreditCardInstallmentRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Api\Search\FilterGroup;
use Magento\Framework\Api\FilterBuilder;



use Odontoprev\Checkout\Model\PaymentDetailsCustom;
use Odontoprev\MipIntegration\Model\InstallmentOption;

class CheckoutHelper extends AbstractHelper
{    
    /**
     * @var CartRepositoryInterface
     */
    protected $cartRepositoryInterface;

    /**
     * @var ObjectManager
     */
    protected $objectManager;
    
    public function __construct(
        CartRepositoryInterface $cartRepository,
        CreditCardInstallmentRepositoryInterface $creditCardInstallmentRepository,
        SearchCriteriaInterface $searchCriteria,
        FilterGroup $filterGroup,
        FilterBuilder $filterBuilder
    ) {
        $this->cartRepository = $cartRepository;
        $this->creditCardInstallmentRepository = $creditCardInstallmentRepository;
        $this->searchCriteria = $searchCriteria;
        $this->filterGroup = $filterGroup;
        $this->filterBuilder = $filterBuilder;

        $this->objectManager = \Magento\Framework\App\ObjectManager::getInstance();
    }

    public function getListInstallmentOptions($cartId, $totals)
    {
        $installmentOptionsActives = $this->getInstallmentOptionsActives();

        $installmentOptions = array();

        if(
            !is_object($installmentOptionsActives) 
            || !$installmentOptionsActives->getItems()
        ) {
            return $installmentOptions;
        }

        // Caso a opção selecionada seja mensal, será retornado a opção de parcelamento 1
        if(!$this->validPeriodProduct($cartId, 'anual')) {
            return [
               $this->createObjectInstallmentOption(1, 1, $totals)
            ];
        }
            
        // Formata o resultado da busca
        foreach ($installmentOptionsActives->getItems() as $key => $installment) {
            if (empty($installment)){
                continue;
            }

            $aux = $installment->getNumberInstallment();

            $installmentOptions[] = $this->createObjectInstallmentOption(
                $installment->getCreditCardInstallmentId(),
                $aux,
                number_format(($totals / $aux), 2, '.', '')
            );
        }

        return $installmentOptions;
    }

    private function getObjectInstallmentOption(){
        return $this->objectManager->create(
            'Odontoprev\MipIntegration\Model\InstallmentOption'
        );
    }

    private function setObjectInstallmentOption(
        $installmentOption, $id, $installment, $valueInstallment
    ) {
        return $installmentOption->setInstallmentId(
            $id
        )->setInstallment(
            $installment
        )->setValueInstallment(
            $valueInstallment
        );
    }

    private function createObjectInstallmentOption($id, $installment, $valueInstallment){
        $installmentOption = $this->getObjectInstallmentOption();

        return $this->setObjectInstallmentOption(
            $installmentOption, 
            $id,
            $installment, 
            $valueInstallment
        );
    }

    private function getInstallmentOptionsActives()
    {
        // Seta o Grupo de filtros
        $this->filterGroup->setFilters([
            $this->filterBuilder
                ->setField('is_active')
                ->setConditionType('eq')
                ->setValue(true)
                ->create()
        ]);

        // Seta os Critérios de busca
        $this->searchCriteria->setFilterGroups([$this->filterGroup]);

        // Realiza a busca - Apenas parcelas ativas
        $installmentOptionsActives = $this->creditCardInstallmentRepository
            ->getList($this->searchCriteria);

        return $installmentOptionsActives;
    }
    
    private function validPeriodProduct($cartId, $period = 'anual')
    {
        $activeCart = $this->getCartActive($cartId);
        $quoteItem  = $this->getFirstItemCart($activeCart);

        $sku = $quoteItem->getProduct()->getSku();

//        if(empty($sku))
//            return FALSE;

        return stristr($sku, $period) !== FALSE ? TRUE : FALSE;
    }

    public function getFirstItemCart($cart)
    {
        return current($cart->getAllItems());
    }

    private function getCartActive($cartId)
    {
        return $this->cartRepository->getActive($cartId);
    }
}
