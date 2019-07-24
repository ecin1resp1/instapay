<?php

namespace ECInternet\InstaPAY\Plugin\Checkout\Model;

class PaymentInformationManagement
{
    /**
     * @var \Magento\Quote\Model\QuoteRepository
     */
    protected $quoteRepository;

    /**
     * @param \Magento\Quote\Model\QuoteRepository $quoteRepository
     */
    public function __construct(
        \Magento\Quote\Model\QuoteRepository $quoteRepository
    ) {
        $this->quoteRepository = $quoteRepository;
    }

    public function beforeSavePaymentInformationAndPlaceOrder(
        \Magento\Checkout\Model\PaymentInformationManagement $subject,
        $cartId,
        \Magento\Quote\Api\Data\PaymentInterface $paymentMethod,
        \Magento\Quote\Api\Data\AddressInterface $billingAddress = null
    ) {
        $extAttributes = $paymentMethod->getExtensionAttributes();
        $instapay_payment_id = $extAttributes->getInstapayPaymentId();
        $quote = $this->quoteRepository->getActive($cartId);
        $quote->setInstapayPaymentId($instapay_payment_id);
        $quote->save();
    }
}
