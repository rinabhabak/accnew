<?php
/**
 * Indusnet Technologies.
 *
 * @category  Indusnet
 * @package   Int_StoreConfigGraphQl
 * @author    Indusnet
 */

namespace Int\StoreConfigGraphQl\Model\Resolver;

use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Exception\GraphQlNoSuchEntityException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Store\Model\ScopeInterface;

/**
 * Class CheckoutActonUrl
 * @package Int\StoreConfigGraphQl\Model\Resolver
 */
class CheckoutActonUrl implements ResolverInterface
{
    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $_scopeConfig;

    const CHECKOUT_ACTON_URL = 'alpine_acton/forms/checkout_newsletter_subscribe_action_url';

    /**
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeInterface
     */
    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeInterface
    ) {
        $this->_scopeConfig = $scopeInterface;
    }

    /**
     * @inheritdoc
     */
    public function resolve(
        Field $field,
        $context,
        ResolveInfo $info,
        array $value = null,
        array $args = null
    ) {
       return (string) $this->_scopeConfig->getValue(
            self::CHECKOUT_ACTON_URL, 
            ScopeInterface::SCOPE_STORE
        );
    }
}