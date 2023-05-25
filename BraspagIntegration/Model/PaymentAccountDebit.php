<?php
namespace Odontoprev\BraspagIntegration\Model;

use Magento\Framework\DataObject\IdentityInterface;
use Odontoprev\BraspagIntegration\Api\Data\PaymentAccountDebitInterface;
use Magento\Framework\Model\AbstractModel;

class PaymentAccountDebit extends AbstractModel implements IdentityInterface, PaymentAccountDebitInterface
{
	 const CACHE_TAG = 'odontoprev_braspagintegration_paymentaccountdebit';

	 /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(
            'Odontoprev\BraspagIntegration\Model\ResourceModel\PaymentAccountDebit'
        );
    }

    public function getIdentities()
    {
        return [self::CACHE_TAG . '_' . $this->getId()];
    }

    /**
     * Get getAccountDebitId
     *
     * @return int|null
     */
    public function getAccountDebitId() {
        return $this->getData(self::ACCOUNT_DEBIT_ID);
    }

    /**
     * Set accountDebitId
     *
     * @param int $accountDebitId
     * @return $this
     */
    public function setAccountDebitId($accountDebitId) {
        return $this->setData(self::ACCOUNT_DEBIT_ID, $accountDebitId);
    }

    /**
     * Get orderId
     *
     * @return int|null
     */
    public function getOrderId() {
        return $this->getData(self::ORDER_ID);
    }

    /**
     * Set orderId
     *
     * @param int $orderId
     * @return $this
     */
    public function setOrderId($orderId) {
        return $this->setData(self::ORDER_ID, $orderId);
    }

    /**
     * Get account
     *
     * @return string|null
     */
    public function getAccount() {
        return $this->getData(self::ACCOUNT);
    }

    /**
     * Set account
     *
     * @param string $account
     * @return $this
     */
    public function setAccount($account) {
        return $this->setData(self::ACCOUNT, $account);
    }

    /**
     * Get accountDv
     *
     * @return string|null
     */
    public function getAccountDv() {
        return $this->getData(self::ACCOUNT_DV);
    }

    /**
     * Set accountDv
     *
     * @param string $accountDv
     * @return $this
     */
    public function setAccountDv($accountDv) {
        return $this->setData(self::ACCOUNT_DV, $accountDv);
    }

    /**
     * Get agency
     *
     * @return string|null
     */
    public function getAgency() {
        return $this->getData(self::AGENCY);
    }

    /**
     * Set agency
     *
     * @param string $agency
     * @return $this
     */
    public function setAgency($agency) {
        return $this->setData(self::AGENCY, $agency);
    }

    /**
     * Get agencyDv
     *
     * @return string|null
     */
    public function getAgencyDv() {
        return $this->getData(self::AGENCY_DV);
    }

    /**
     * Set agencyDv
     *
     * @param string $agencyDv
     * @return $this
     */
    public function setAgencyDv($agencyDv) {
        return $this->setData(self::AGENCY_DV, $agencyDv);
    }

    /**
     * Get bankCode
     *
     * @return string|null
     */
    public function getBankCode() {
        return $this->getData(self::BANK_CODE);
    }

    /**
     * Set bankCode
     *
     * @param string $bankCode
     * @return $this
     */
    public function setBankCode($bankCode) {
        return $this->setData(self::BANK_CODE, $bankCode);
    }
}