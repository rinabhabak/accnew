<?php
/**
 * Alpine_Acton
 *
 * @category    Alpine
 * @package     Alpine_Acton
 * @copyright   Copyright (c) 2018 Alpine Consulting, Inc (www.alpineinc.com)
 * @author      Aleksandr Mikhailov (aleksandr.mikhailov@alpineinc.com)
 */

namespace Alpine\Acton\Block;

use Magento\Checkout\Model\Session;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\View\Element\Template;
use Magento\Store\Model\ScopeInterface;

/**
 * Class CheckoutSuccessIframe
 *
 * @category    Alpine
 * @package     Alpine_Acton
 */
class CheckoutSuccessIframe extends Template
{
    /**
     * Action URL for subscription for newsletter in checkout
     *
     * @var string
     */
    const XML_PATH_ACTION_URL_SUBSCRIPTION_FOR_NEWSLETTER = 'alpine_acton/forms/checkout_newsletter_subscribe_action_url';

    /**
     * JSON Serializer
     *
     * @var Json
     */
    protected $jsonSerializer;

    /**
     * Session
     *
     * @var Session
     */
    protected $session;

    /**
     * Scope Config
     *
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * CheckoutSuccessIframe constructor
     *
     * @param Template\Context     $context
     * @param Session              $session
     * @param ScopeConfigInterface $scopeConfig
     * @param Json                 $jsonSerializer
     * @param array                $data
     */
    public function __construct(
        Template\Context $context,
        Session $session,
        ScopeConfigInterface $scopeConfig,
        Json $jsonSerializer,
        array $data = []
    ) {
        $this->session         = $session;
        $this->scopeConfig     = $scopeConfig;
        $this->jsonSerializer  = $jsonSerializer;
        parent::__construct($context, $data);

    }

    /**
     * Get config for "place-acton-iframe"
     *
     * @return string
     */
    public function getPlaceActonIframeConfig()
    {
        $order = $this->session->getLastRealOrder();
        
        $actionUrl = $this->scopeConfig->getValue(
            self::XML_PATH_ACTION_URL_SUBSCRIPTION_FOR_NEWSLETTER,
            ScopeInterface::SCOPE_STORE
        );

        $config = [
            'first_name'   => $order->getBillingAddress()->getFirstName(),
            'last_name'    => $order->getBillingAddress()->getLastname(),
            'email'        => $order->getCustomerEmail(),
            'company'      => $order->getShippingAddress()->getCompany() ?? '',
            'phone_number' => $order->getShippingAddress()->getTelephone(),
            'actionUrl'    => $actionUrl,

        ];

        return $this->jsonSerializer->serialize($config);
    }
}