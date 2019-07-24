<?php
/* Copyright (C) EC Brands Corporation - All Rights Reserved
** Contact Licensing@ECInternet.com for use guidelines
*/

namespace ECInternet\InstaPAY\Controller\Payment;

use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Framework\HTTP\Client\Curl;
use ECInternet\InstaPAY\Helper\Data as InstaPAYHelper;

class Quicksale extends \Magento\Framework\App\Action\Action
{
	/**
	 * @var JsonFactory
	 */
	private $resultJsonFactory;

	/**
	 * @var CheckoutSession
	 */
	private $checkoutSession;

	/**
	 * @var \Magento\Store\Model\StoreManagerInterface
	 */
	private $storeManager;

	/**
	 * @var Curl
	 */
	private $curl;

	/**
	 * @var InstaPAYHelper
	 */
	private $instaPayHelper;

	/**
	 * @param Context $context
	 * @param JsonFactory $resultJsonFactory
	 * @param CheckoutSession $checkoutSession
	 * @param \Magento\Store\Model\StoreManagerInterface $storeManager
	 * @param Curl $curl
	 * @param InstaPAYHelper $instaPayHelper
	 */
	public function __construct(
	    Context $context,
	    JsonFactory $resultJsonFactory,
	    CheckoutSession $checkoutSession,
	    \Magento\Store\Model\StoreManagerInterface $storeManager,
	    Curl $curl,
	    InstaPAYHelper $instaPayHelper,
	    array $data = []
	) {
	    $this->resultJsonFactory = $resultJsonFactory;
	    $this->checkoutSession = $checkoutSession;
	    $this->storeManager = $storeManager;
	    $this->curl = $curl;
	    $this->instaPayHelper = $instaPayHelper;
	    parent::__construct($context, $data);
	}

	/**
	 * Get files data and upload files to the server
	 * @return json
	 * @throws \Magento\Framework\Exception\NoSuchEntityException
	 */
    public function execute()
    {
    	$result = $this->resultJsonFactory->create();
    	$url = 'https://trans.instapaygateway.com/cgi-bin/process.cgi';
    	try {
	    	$params = json_decode($this->getRequest()->getContent(), true);
	    	$quote = $this->checkoutSession->getQuote();
	    	$grandTotal = number_format((float)$quote->getGrandTotal(), 2, '.', '');
	    	if($quote->getCustomerId()) {
				$customerId = $quote->getCustomerId();
			} else {
				$customerId = 'GUEST';
			}
			$time = date('h') . date('i') . date('s');
	    	$merchantOrderNumber = $customerId . '-' . $time;

	    	$quoteBillingAddress = $quote->getBillingAddress();
        	$quoteShippingAddress = $quote->getShippingAddress();
        	
        	$billing_address_street = $quoteBillingAddress->getStreet();
        	$params['ci_billaddr1'] = $billing_address_street[0];
	        if ($quoteBillingAddress->getCompany()) {
	            $params['ci_companyname'] = $quoteBillingAddress->getCompany();
	        } else {
	            $params['ci_companyname'] = '';
	        }
	        if (array_key_exists(1, $billing_address_street)) {
	            $params['ci_billaddr2'] = $billing_address_street[1];
	        } else {
	            $params['ci_billaddr2'] = '';
	        }
	        $params['ci_billcity'] = $quoteBillingAddress->getCity();
	        $params['ci_billstate'] = $quoteBillingAddress->getRegionCode();
	        $params['ci_billcountry'] = $quoteBillingAddress->getCountry();
	        $params['ci_billzip'] = $quoteBillingAddress->getPostcode();
	        $params['ci_phone'] = $quoteBillingAddress->getTelephone();
	        $params['ci_email'] = $quoteBillingAddress->getEmail();

	        $shipping_address_street = $quoteShippingAddress->getStreet();
        	$params['ci_shipaddr1'] = $shipping_address_street[0];
	        if (array_key_exists(1, $shipping_address_street)) {
	            $params['ci_shipaddr2'] = $shipping_address_street[1];
	        } else {
	            $params['ci_shipaddr2'] = '';
	        }
	        $params['ci_shipcity'] = $quoteShippingAddress->getCity();
	        $params['ci_shipstate'] = $quoteShippingAddress->getRegionCode();
	        $params['ci_shipcountry'] = $quoteShippingAddress->getCountry();
	        $params['ci_shipzip'] = $quoteShippingAddress->getPostcode();
	    	$params['action'] = 'ns_quicksale_cc';
	    	$params['acctid'] = $this->instaPayHelper->getAccountId();
	    	$params['subid'] = $this->instaPayHelper->getSubId();
	    	$params['merchantpin'] = $this->instaPayHelper->getMerchantPin();
	    	$params['amount'] = $grandTotal;
	    	$params['merchantordernumber'] = $merchantOrderNumber;
    		$this->curl->get($url);
    		$this->curl->post($url, $params);
    		$response = $this->curl->getBody();
			$result->setData(['status' => 1]);
			return $result;
		} catch (Exception $e) {
			$result->setData(['status' => 0]);
			return $result;
		}
    }
}
