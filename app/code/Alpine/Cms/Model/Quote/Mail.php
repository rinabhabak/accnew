<?php
/**
 * Mail Quote Form Model
 *
 * @category    Alpine
 * @package     Alpine_Cms
 * @copyright   Copyright (c) 2018 Alpine Consulting Inc.
 * @author      Dmitry Naumov <dmitry.naumov@alpineinc.com>
 */

namespace Alpine\Cms\Model\Quote;

use Magento\Framework\Mail\Template\TransportBuilder;
use Magento\Framework\Translate\Inline\StateInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Contact\Model\Mail as ContactMail;

/**
 * Mail Quote Form Model
 *
 * @category    Alpine
 * @package     Alpine_Cms
 */
class Mail extends ContactMail
{
    /**
     * Initialize dependencies.
     *
     * @param Config $config
     * @param TransportBuilder $transportBuilder
     * @param StateInterface $inlineTranslation
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        Config $config,
        TransportBuilder $transportBuilder,
        StateInterface $inlineTranslation,
        StoreManagerInterface $storeManager
    ) {
        parent::__construct($config, $transportBuilder, $inlineTranslation, $storeManager);
    }
}