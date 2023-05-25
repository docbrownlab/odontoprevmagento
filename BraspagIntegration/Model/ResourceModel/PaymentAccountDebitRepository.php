<?php
namespace Odontoprev\BraspagIntegration\Model\ResourceModel;

use Odontoprev\BraspagIntegration\Model\PaymentAccountDebitFactory;
use Magento\Framework\Exception\CouldNotSaveException;
use Odontoprev\BraspagIntegration\Api\PaymentAccountDebitRepositoryInterface;
use Odontoprev\BraspagIntegration\Api\Data\PaymentAccountDebitInterface;

class PaymentAccountDebitRepository implements PaymentAccountDebitRepositoryInterface
{

	private $paymentAccountDebitFactory;

    public function __construct(
    	PaymentAccountDebitFactory $paymentAccountDebitFactory
    )
    {
        $this->paymentAccountDebitFactory = $paymentAccountDebitFactory;
    }

    /**
     * Get account debit from order id
     *
     * @param string $orderId
     * @return \Odontoprev\BraspagIntegration\Api\Data\PaymentAccountDebitInterface paymentAccountDebit
     */
    public function get($orderId)
    {
        return $this->paymentAccountDebitFactory
        	->create()
            ->getCollection()
            ->addFieldToFilter('order_id', array('eq' => $orderId))
            ->getFirstItem();
    }

    /**
     * Add/update the specified account debit.
     *
     * @param \Odontoprev\BraspagIntegration\Api\Data\PaymentAccountDebitInterface $accountDebit
     * @return \Odontoprev\BraspagIntegration\Api\Data\PaymentAccountDebitInterface paymentAccountDebit
     * @throws CouldNotSaveException
     */
    public function save(
    	PaymentAccountDebitInterface $accountDebit
    )
    {
        try {
            return $accountDebit->save();
        } catch (\Exception $e) {
            throw new CouldNotSaveException(__('Could not save account debit.'));
        }
    }
}