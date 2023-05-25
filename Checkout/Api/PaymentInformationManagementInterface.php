<?php
namespace Odontoprev\Checkout\Api;

interface PaymentInformationManagementInterface
{
    /**
     * Set payment information and place order for a specified cart.
     *
     * @param int $cartId
     * @param \Magento\Quote\Api\Data\PaymentInterface $paymentMethod
     * @param \Magento\Quote\Api\Data\AddressInterface $billingAddress
     * @param string $brokerCode
     * @param string $couponCode
     * @param mixed @processingQueue
     * @param mixed @accountDebit
     * @param mixed $retailerId
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     * @return array Order IDs.
     */
    public function savePaymentInformationAndPlaceOrder(
        $cartId,
        \Magento\Quote\Api\Data\PaymentInterface $paymentMethod,
        \Magento\Quote\Api\Data\AddressInterface $billingAddress = NULL,
        $brokerCode = NULL,
        $couponCode = NULL,
        $processingQueue = NULL,
        $accountDebit = NULL,
        $retailerId = NULL
    );

    /**
     * Get payment information
     *
     * @param int $cartId
     * @return \Odontoprev\Checkout\Api\PaymentDetailsCustomInterface
     */
    public function getPaymentInformation($cartId);
}
