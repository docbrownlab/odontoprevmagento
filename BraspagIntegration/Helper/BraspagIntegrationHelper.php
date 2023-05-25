<?php

namespace Odontoprev\BraspagIntegration\Helper;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\App\Helper\AbstractHelper;
use Odontoprev\BraspagIntegration\Model\PaymentAccountDebitFactory;
use Odontoprev\BraspagIntegration\Model\ResourceModel\PaymentAccountDebitRepository;
use Odontoprev\BraspagIntegration\Model\Ui\PaymentAccountDebit;
use Odontoprev\BraspagIntegration\Model\Ui\ConfigProvider;
use \Magento\Store\Model\ScopeInterface;

class BraspagIntegrationHelper extends AbstractHelper
{
    protected $storeId;

    protected $configTable = 'core_config_data';

    /**
     * @var ResourceConnection
     */
    protected $resourceConnection;
    
    /**
     * @var PaymentAccountDebitFactory
     */
    protected $paymentAccountDebitFactory;

    /**
     * @var PaymentAccountDebitRepository
     */
    protected $paymentAccountDebitRepository;

    protected $scopeConfig;
    
    public function __construct(
        ResourceConnection $resourceConnection,
        PaymentAccountDebitFactory $paymentAccountDebitFactory,
        PaymentAccountDebitRepository $paymentAccountDebitRepository,
        ScopeConfigInterface $scopeConfig
    ) 
    {
        $this->resourceConnection               = $resourceConnection->getConnection();
        $this->paymentAccountDebitFactory       = $paymentAccountDebitFactory;
        $this->paymentAccountDebitRepository    = $paymentAccountDebitRepository;
        $this->scopeConfig = $scopeConfig;
    }

    public function calculateAgencyCheckDigit($agency)
    {
        $checkDigit = (string) $this->calculateModule11($agency, 5, true);

        if($checkDigit == '10')
            return 'P';

        if($checkDigit == '11')
            return '0';

        return $checkDigit;
    }

    public function calculateModule11($num, $base=9, $rest = false) 
    {
        $sum    = 0;
        $factor = 2;
        
        for ($i = strlen($num); $i > 0; $i--) {
            $aux    = substr($num,$i-1,1);
            $sum    += ($aux * $factor);
            $factor = $factor == $base ? 1 : $factor+1;
        }

        $sum   *= 10;
        $digit = $sum % 11;

        if ($rest == 0)
            return $digit;
        
        return $digit == 10 ? 0 : $digit;
    }

    public function getAccountDebit($orderId) 
    {
        if(is_null($orderId))
            return [];

        return $this->paymentAccountDebitRepository->get($orderId);
    }

    public function getCodeSendAccountDebit($bankCode, $storeId)
    {
        //payment/braspag_integration_billet_account_debit/bank_list
        $paymentList = $this->scopeConfig
            ->getValue('payment/braspag_integration_billet_account_debit/bank_list',
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                $storeId
            );

        $listOfBanks = explode(';', $paymentList) ;

        $organizedBankData = [];

        foreach ($listOfBanks as $key => $value) {
            $bankData = explode(',', $value);
            $organizedBankData[$bankData[1]] = $bankData[2];
        }

        return isset($organizedBankData[$bankCode]) ? $organizedBankData[$bankCode] : NULL;
    }

    public function keysBraspagIntegration($withKeys = false) 
    {
        $keyBillet      = ConfigProvider::CODE;
        $accountDebit   = PaymentAccountDebit::CODE;

        if(! $withKeys)
            return [$keyBillet, $accountDebit];

        return [
            'billet'        => $keyBillet,
            'accountDebit'  => $accountDebit 
        ];
    }
}
