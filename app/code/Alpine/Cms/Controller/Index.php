<?php
/**
 * Quote form base controller
 *
 * @category    Alpine
 * @package     Alpine_Cms
 * @copyright   Copyright (c) 2018 Alpine Consulting Inc.
 * @author      Dmitry Naumov <dmitry.naumov@alpineinc.com>
 */

namespace Alpine\Cms\Controller;

use Alpine\Cms\Model\Quote\Config;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Exception\NotFoundException;

/**
 * Quote form base controller
 *
 * @category    Alpine
 * @package     Alpine_Cms
 */
abstract class Index extends Action
{
    /**
     * Recipient email config path
     *
     * @var string
     */
    const XML_PATH_EMAIL_RECIPIENT = Config::XML_PATH_EMAIL_RECIPIENT;

    /**
     * Sender email config path
     *
     * @var string
     */
    const XML_PATH_EMAIL_SENDER = Config::XML_PATH_EMAIL_SENDER;

    /**
     * Email template config path
     *
     * @var string
     */
    const XML_PATH_EMAIL_TEMPLATE = Config::XML_PATH_EMAIL_TEMPLATE;

    /**
     * Enabled config path
     *
     * @var string
     */
    const XML_PATH_ENABLED = Config::XML_PATH_ENABLED;

    /**
     * Quote config
     *
     * @var Config
     */
    private $quoteConfig;

    /**
     * Index constructor
     *
     * @param Context $context
     * @param Config $quoteConfig
     */
    public function __construct(Context $context, Config $quoteConfig)
    {
        parent::__construct($context);
        $this->quoteConfig = $quoteConfig;
    }

    /**
     * Dispatch request
     *
     * @param RequestInterface $request
     * @return ResponseInterface
     * @throws NotFoundException
     */
    public function dispatch(RequestInterface $request)
    {
        if (!$this->quoteConfig->isEnabled()) {
            throw new NotFoundException(__('Page not found.'));
        }
        return parent::dispatch($request);
    }
}