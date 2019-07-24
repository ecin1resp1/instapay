/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'jquery',
    'mage/utils/wrapper'
], function ($, wrapper) {
    'use strict';

    return function (placeOrderAction) {

        /** Override default place order action and add agreement_ids to request */
        return wrapper.wrap(placeOrderAction, function (originalAction, paymentData, messageContainer) {

            if (paymentData['extension_attributes'] === undefined) {
                paymentData['extension_attributes'] = {};
            }

            var instapay_payment_id = $('input[name="instapay_payment_id"]').val();
            paymentData['extension_attributes']['instapay_payment_id'] = instapay_payment_id;
            return originalAction(paymentData, messageContainer);
        });
    };
});
