/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
/*browser:true*/
/*global define*/
define(
    [
        'uiComponent',
        'Magento_Checkout/js/model/payment/renderer-list'
    ],
    function (
        Component,
        rendererList
    ) {
        'use strict';
        rendererList.push(
            {
                type: 'braspag_integration_billet',
                component: 'Odontoprev_BraspagIntegration/js/view/payment/method-renderer/braspag_integration_billet'
            },
            {
                type: 'braspag_integration_billet_account_debit',
                component: 'Odontoprev_BraspagIntegration/js/view/payment/method-renderer/braspag_integration_billet_account_debit'
            }
        );
        /** Add view logic here if needed */
        return Component.extend({});
    }
);
