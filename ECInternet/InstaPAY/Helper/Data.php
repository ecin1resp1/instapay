<?php
/* Copyright (C) EC Brands Corporation - All Rights Reserved
** Contact Licensing@ECInternet.com for use guidelines
*/

namespace ECInternet\InstaPAY\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Zend\Log\Logger;
use Zend\Log\Writer\Stream as WriterStream;

/**
 * Class Data
 *
 * @package ECInternet_InstaPAY
 */
class Data extends AbstractHelper
{
	const LOG_PATH = "/var/log/ECInternet_InstaPAY.log";
	const ACCOUNT_ID = "payment/instapay/account_id";
	const SUB_ID = "payment/instapay/sub_id";
	const MERCHANT_PIN = "payment/instapay/merchant_pin";
	const ATTRIBUTE_INSTAPAY_PAYMENT_ID = "instapay_payment_id";

	/**
	 * @var \Magento\Framework\App\Config\ScopeConfigInterface
	 */
	protected $scopeConfig;

	/**
	 * @var \Zend\Log\Writer\Stream
	 */
	private $_writer;

	/**
	 * @var \Zend\Log\Logger
	 */
	protected $_logger;

	/**
	 * @param Context $context
	 * @param ScopeConfigInterface $scopeConfig
	 */
	public function __construct(
		Context $context,
		ScopeConfigInterface $scopeConfig
	) {
		$this->scopeConfig = $scopeConfig;
		parent::__construct($context);
		$this->initLogger();
	}

	public function getMerchantPin()
	{
		return $this->getConfig(self::MERCHANT_PIN);
	}

	public function getAccountId()
	{
		return $this->getConfig(self::ACCOUNT_ID);
	}

	public function getSubId()
	{
		return $this->getConfig(self::SUB_ID);
	}

	/**
	 * Get system configuration value.
	 *
	 * @param string $message
	 *
	 * @return void
	 */
	public function getConfig($value)
	{
		return $this->scopeConfig->getValue($value, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
	}

	/**
	 * Write to extension log
	 * @param string $message
	 * @return void
	 */
	public function log($message)
	{
		$this->_logger->info($message);
	}

	/**
	 * @return void
	 */
	protected function initLogger()
	{
		/** @var \Zend\Log\Writer\Stream $writer */
		$this->_writer = new WriterStream(BP . self::LOG_PATH);

		/** @var \Zend\Log\Logger _logger */
		$this->_logger = new Logger();
		$this->_logger->addWriter($this->_writer);
	}
}
