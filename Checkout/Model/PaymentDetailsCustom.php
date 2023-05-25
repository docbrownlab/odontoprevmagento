<?php
namespace Odontoprev\Checkout\Model;

use Odontoprev\Checkout\Api\PaymentDetailsCustomInterface;
use Magento\Framework\Model\AbstractExtensibleModel;
use Magento\Checkout\Model\PaymentDetails;

/**
 * @codeCoverageIgnoreStart
 */
class PaymentDetailsCustom extends PaymentDetails implements PaymentDetailsCustomInterface
{
    /**
     * @{inheritdoc}
     */
    public function getBankList()
    {
        return $this->getData(self::BANK_LIST);
    }
    
    /**
     * @{inheritdoc}
     */
    public function setBankList($bankList)
    {
        return $this->setData(self::BANK_LIST, $bankList);
    }

    /**
     * @{inheritdoc}
     */
    public function getFormPaymentInstallment()
    {
        return $this->getData(self::FORM_PAYMENT_INSTALLMENT);
    }

    /**
     * @{inheritdoc}
     */
    public function setFormPaymentInstallment($formPaymentInstallment)
    {
       return $this->setData(self::FORM_PAYMENT_INSTALLMENT, $formPaymentInstallment);
    }

    /**
     * @{inheritdoc}
     */
    public function getInstallmentOptions()
    {
        return $this->getData(self::INSTALLMENT_OPTIONS);
    }

    /**
     * @{inheritdoc}
     */
    public function setInstallmentOptions($installmentOptions)
    {
        return $this->setData(self::INSTALLMENT_OPTIONS, $installmentOptions);
    }
}
