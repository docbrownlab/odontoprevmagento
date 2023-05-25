<?php
namespace Odontoprev\BraspagIntegration\Api\Data;

use Magento\Framework\Api\ExtensibleDataInterface;

interface PaymentAccountDebitInterface extends ExtensibleDataInterface
{

    const ACCOUNT_DEBIT_ID = 'entity_id';

    const ORDER_ID = 'order_id';

    const ACCOUNT = 'account';

    const ACCOUNT_DV = 'account_dv';

    const AGENCY = 'agency';

    const AGENCY_DV = 'agency_dv';

    const BANK_CODE = 'bank_code';

    /**
     * get accountDebitId
     *
     * @return int|null
     */
    public function getAccountDebitId();

    /**
     * Set accountDebitId
     *
     * @param int $accountDebitId
     * @return $this
     */
    public function setAccountDebitId($accountDebitId);

    /**
     * Get orderId
     *
     * @return int|null
     */
    public function getOrderId();

    /**
     * Set orderId
     *
     * @param int $orderId
     * @return $this
     */
    public function setOrderId($orderId);

    /**
     * Get account
     *
     * @return string|null
     */
    public function getAccount();

    /**
     * Set account
     *
     * @param string $account
     * @return $this
     */
    public function setAccount($account);

    /**
     * Get accountDv
     *
     * @return string|null
     */
    public function getAccountDv();

    /**
     * Set accountDv
     *
     * @param string $accountDv
     * @return $this
     */
    public function setAccountDv($accountDv);

    /**
     * Get agency
     *
     * @return string|null
     */
    public function getAgency();

    /**
     * Set agency
     *
     * @param string $agency
     * @return $this
     */
    public function setAgency($agency);

    /**
     * Get agencyDv
     *
     * @return int|null
     */
    public function getAgencyDv();

    /**
     * Set agencyDv
     *
     * @param int $agencyDv
     * @return $this
     */
    public function setAgencyDv($agencyDv);

    /**
     * Get bankCode
     *
     * @return int|null
     */
    public function getBankCode();

    /**
     * Set bankCode
     *
     * @param int $bankCode
     * @return $this
     */
    public function setBankCode($bankCode);
}