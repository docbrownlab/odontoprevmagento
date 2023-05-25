<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Odontoprev\BraspagIntegration\Model;

/**
 * Pay In Store payment method model
 */
class OdontoBillet extends \Magento\Payment\Model\Method\AbstractMethod {
    /**
     * Payment code
     *
     * @var string
     */
    protected $_code = 'braspag_integration_billet';

    /**
     * Payment Method feature
     *
     * @var bool
     */
    protected $_canAuthorize = true;
}
