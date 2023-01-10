<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_ShopbyBrandGraphQl
 */


declare(strict_types=1);

namespace Amasty\ShopbyBrandGraphQl\Model\Resolver;

use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Store\Model\ScopeInterface;
use Amasty\ShopbyBrand\Block\Widget\BrandSlider as BrandsWidget;

class BrandSlider implements ResolverInterface
{
    /**
     * @var BrandsWidget
     */
    private $brandSlider;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    public function __construct(
        BrandsWidget $brandSlider,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
    ) {
        $this->brandSlider = $brandSlider;
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * @param Field $field
     * @param \Magento\Framework\GraphQl\Query\Resolver\ContextInterface $context
     * @param ResolveInfo $info
     * @param array|null $value
     * @param array|null $args
     * @return array|\Magento\Framework\GraphQl\Query\Resolver\Value|mixed
     * @throws \Exception
     */
    public function resolve(
        Field $field,
        $context,
        ResolveInfo $info,
        array $value = null,
        array $args = null
    ) {
        $data['items'] = $this->brandSlider->getItems();

        $configValues = $this->scopeConfig->getValue(
            BrandsWidget::CONFIG_VALUES_PATH,
            ScopeInterface::SCOPE_STORE
        );

        return array_merge($data, $configValues);
    }
}
