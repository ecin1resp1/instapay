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
                type: 'instapay',
                component: 'ECInternet_InstaPAY/js/view/payment/method-renderer/instapay'
            }
        );
        return Component.extend({});
    }
);
