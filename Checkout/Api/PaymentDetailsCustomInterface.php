<?php
namespace Odontoprev\Checkout\Api;

use Magento\Checkout\Api\Data\PaymentDetailsInterface;

/**
 * Interface PaymentDetailsCustomInterface
 * @api
 */
interface PaymentDetailsCustomInterface extends PaymentDetailsInterface
{
    /**#@+
     * Constants defined for keys of array, makes typos less likely
     */
    const BANK_LIST = 'bank_list';

    const INSTALLMENT_OPTIONS = 'installment_options';

    const FORM_PAYMENT_INSTALLMENT = 'form_payment_installment';
    
    /**#@-*/

    /**
     * @return
     */
    public function getBankList();

    /**
     * @return $this
     */
    public function setBankList($bankList);

    /**
     * @return
     */
    public function getFormPaymentInstallment();

    /**
     * @return $this
     */
    public function setFormPaymentInstallment($formPaymentInstallment);

	/**
     * @return \Odontoprev\MipIntegration\Api\Data\InstallmentOptionInterface[]
     */
    public function getInstallmentOptions();

    /**
     * @param \Odontoprev\MipIntegration\Api\Data\InstallmentOptionInterface[] $installmentOptions
     * @return $this
     */
    public function setInstallmentOptions($installmentOptions);
}