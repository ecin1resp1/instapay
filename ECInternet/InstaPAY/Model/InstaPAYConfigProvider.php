<?php

namespace ECInternet\InstaPAY\Model;

use Magento\Checkout\Model\ConfigProviderInterface;
use Magento\Framework\View\Asset\Source;

class InstaPAYConfigProvider implements ConfigProviderInterface
{
    /**
    * @param CcConfig $ccConfig
    * @param Source $assetSource
    */
    public function __construct(
        \Magento\Payment\Model\CcConfig $ccConfig,
        Source $assetSource
    ) {
        $this->ccConfig = $ccConfig;
        $this->assetSource = $assetSource;
    }

    /**
    * @var string
    */
    protected $_methodCode = 'instapay';

    /**
    * {@inheritdoc}
    */
    public function getConfig()
    {
        return [
            'payment' => [
                'instapay' => [
                    'availableTypes' => [$this->_methodCode => $this->ccConfig->getCcAvailableTypes()],
                    'months' => [$this->_methodCode => $this->ccConfig->getCcMonths()],
                    'years' => [$this->_methodCode => $this->ccConfig->getCcYears()],
                    'hasVerification' => [$this->_methodCode => $this->ccConfig->hasVerification()],
                    'cvvImageUrl' => [$this->_methodCode => $this->ccConfig->getCvvImageUrl()],
                ]
            ]
        ];
    }
}
