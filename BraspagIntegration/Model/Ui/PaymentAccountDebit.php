<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Odontoprev\BraspagIntegration\Model\Ui;

use Magento\Checkout\Model\ConfigProviderInterface;
use Odontoprev\BraspagIntegration\Gateway\Http\Client\ClientMock;

/**
 * Class PaymentAccountDebit
 */
final class PaymentAccountDebit implements ConfigProviderInterface
{
    const CODE = 'braspag_integration_billet_account_debit';

    /**
     * Retrieve assoc array of checkout configuration
     *
     * @return array
     */
    public function getConfig()
    {
        return [
            'payment' => [
                self::CODE => [
                    'transactionResults' => [
                        ClientMock::SUCCESS => __('Success'),
                        ClientMock::FAILURE => __('Fraud')
                    ]
                ]
            ]
        ];
    }
}
