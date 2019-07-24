<?php
/* Copyright (C) EC Brands Corporation - All Rights Reserved
** Contact Licensing@ECInternet.com for use guidelines
*/

namespace ECInternet\InstaPAY\Observer;

use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;
use ECInternet\InstaPAY\Helper\Data as InstaPAYHelper;

class SaveDataToOrderObserver implements ObserverInterface
{
	/**
	 * @var \Magento\Quote\Model\QuoteFactory
	 */
    protected $_quoteFactory;

	/**
	 * @var InstaPAYHelper
	 */
    protected $_helper;

	/**
	 * @param \Magento\Quote\Model\QuoteFactory $quoteFactory
	 * @param InstaPAYHelper $helper
	 */
    public function __construct(
	    \Magento\Quote\Model\QuoteFactory $quoteFactory,
	    InstaPAYHelper $helper
    ) {
        $this->_quoteFactory = $quoteFactory;
        $this->_helper = $helper;
    }

    public function execute(EventObserver $observer)
    {
    	/** @var \Magento\Sales\Model\Order $order */
        $order = $observer->getOrder();
        $quoteId = $order->getQuoteId();
        $quote = $this->_quoteFactory->create()->load($quoteId);

        $instapay_payment_id = $quote->getData(InstaPAYHelper::ATTRIBUTE_INSTAPAY_PAYMENT_ID);

        $this->_helper->log('SaveDataToOrderObserver');
	    $this->_helper->log('quoteId: ' . $quoteId);
	    $this->_helper->log('instapay_payment_id: ' . $instapay_payment_id);

        $order->setData(InstaPAYHelper::ATTRIBUTE_INSTAPAY_PAYMENT_ID, $instapay_payment_id);
	    $order->save();

        return $this;
    }
}
