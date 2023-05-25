<?php
namespace Odontoprev\BraspagIntegration\Api;

use Magento\Framework\Api\ExtensibleDataInterface;
use Odontoprev\BraspagIntegration\Api\Data\PaymentAccountDebitInterface;

interface PaymentAccountDebitRepositoryInterface extends ExtensibleDataInterface
{
	/**
     * Get account debit from order id
     *
     * @param int $orderId
     * @return \Odontoprev\BraspagIntegration\Api\Data\PaymentAccountDebitInterface paymentAccountDebit
     */
    public function get($orderId);

    /**
     * Add/update the specified account debit.
     *
     * @param \Odontoprev\BraspagIntegration\Api\Data\PaymentAccountDebitInterface $accountDebit
     * @return \Odontoprev\BraspagIntegration\Api\Data\PaymentAccountDebitInterface paymentAccountDebit
     * @throws CouldNotSaveException
     */
    public function save(
    	PaymentAccountDebitInterface $accountDebit
    );
}