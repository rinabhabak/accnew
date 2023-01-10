<?php
/**
 * Alpine_Reviews Helper
 *
 * @category    Alpine
 * @package     Alpine_Reviews
 * @copyright   Copyright (c) 2018 Alpine Consulting Inc.
 * @author      Lev Zamansky <lev.zamanskiy@alpineinc.com>
 */

namespace Alpine\Reviews\Helper;

use Magento\Framework\App\Config\ScopeConfigInterface;

/**
 * Alpine_Review Helper
 *
 * @category    Alpine
 * @package     Alpine_Reviews
 */
class Data extends \Magento\Framework\App\Helper\AbstractHelper
{

    /**
     * Limit config path
     *
     * @var string
     */
    const LIMIT_CONFIG_PATH = 'alpine_review/settings/pdp_review_count';

    /**
     * Configuration interface
     *
     * @var ScopeConfigInterface
     */
    protected $scopeInterface;

    /**
     * Data constructor
     *
     * @param ScopeConfigInterface $scopeInterface
     */
    public function __construct(
        ScopeConfigInterface $scopeInterface
    ) {
        $this->scopeInterface = $scopeInterface;
    }

    /**
     * Get reviews limit
     *
     * @return string
     */
    public function getLimit() {
        $limit = $this->scopeInterface->getValue(self::LIMIT_CONFIG_PATH, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);

        return $limit;
    }
}
