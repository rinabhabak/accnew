<?php
/**
 * Config Quote Form Model
 *
 * @category    Alpine
 * @package     Alpine_Cms
 * @copyright   Copyright (c) 2018 Alpine Consulting Inc.
 * @author      Dmitry Naumov <dmitry.naumov@alpineinc.com>
 */

namespace Alpine\Cms\Model\Quote;

use Magento\Contact\Model\Config as ContactConfig;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;

/**
 * Quote form Alpine_Cms module configuration
 *
 * @category    Alpine
 * @package     Alpine_Cms
 */
class Config extends ContactConfig
{
    /**
     * Scope config interface
     *
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * Recipient email config path
     *
     * @var string
     */
    const XML_PATH_EMAIL_RECIPIENT = 'alpine_cms_quote/email/recipient_email';

    /**
     * Sender email config path
     *
     * @var string
     */
    const XML_PATH_EMAIL_SENDER = 'alpine_cms_quote/email/sender_email_identity';

    /**
     * Email template config path
     *
     * @var string
     */
    const XML_PATH_EMAIL_TEMPLATE = 'alpine_cms_quote/email/email_template';

    /**
     * Enabled config path
     *
     * @var string
     */
    const XML_PATH_ENABLED = 'alpine_cms_quote/quote/enabled';

    /**
     * Config constructor
     *
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(ScopeConfigInterface $scopeConfig)
    {
        $this->scopeConfig = $scopeConfig;
        parent::__construct($scopeConfig);
    }

    /**
     * {@inheritdoc}
     */
    public function isEnabled()
    {
        return $this->scopeConfig->isSetFlag(
            self::XML_PATH_ENABLED,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * {@inheritdoc}
     */
    public function emailTemplate()
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_EMAIL_TEMPLATE,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * {@inheritdoc}
     */
    public function emailSender()
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_EMAIL_SENDER,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * {@inheritdoc}
     */
    public function emailRecipient()
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_EMAIL_RECIPIENT,
            ScopeInterface::SCOPE_STORE
        );
    }
}