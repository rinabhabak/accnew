<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Int\ConfiguratorQuoteGraphQl\Model\Resolver;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Quote\Model\Quote\Item as QuoteItem;

/**
 * @inheritdoc
 */
class CartProject implements ResolverInterface
{
    protected $_configuratorFactory;
    
    public function __construct(
    \Magento\Catalog\Model\ProductRepository $productRepository,
    \Int\Configurator\Model\ConfiguratorFactory $configuratorFactory
    )
    {
        $this->_configuratorFactory  = $configuratorFactory;
        
    }
    
    public function getProductById($id)
    {
        return $this->_productRepository->getById($id);
    }

    /**
     * @inheritdoc
     */
    public function resolve(Field $field, $context, ResolveInfo $info, array $value = null, array $args = null)
    {
       
        $output = array();
        if (!isset($value['model'])) {
            throw new LocalizedException(__('"model" value should be specified'));
        }

        /** @var QuoteItem $cart */
        $cart = $value['model'];
       
        if (null === $cart || null === $cart->getConfiguratorPid()) {
            return false;
        }
       
        return $cart->getConfiguratorPid();
    }
}
