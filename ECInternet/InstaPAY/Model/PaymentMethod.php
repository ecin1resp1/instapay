<?php

namespace ECInternet\InstaPAY\Model;

/**
 * InstaPAY Payment method model
 */
class PaymentMethod extends \Magento\Payment\Model\Method\AbstractMethod
{
    /**
     * Payment method code
     *
     * @var string
     */
    protected $_code = 'instapay';
}
