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
use Amasty\ShopbyBrand\Block\Widget\BrandList as BrandsWidget;

class BrandList implements ResolverInterface
{
    /**
     * @var BrandsWidget
     */
    private $brandList;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    public function __construct(
        BrandsWidget $brandList,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
    ) {
        $this->brandList = $brandList;
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
        $brands = $this->brandList->getItems();
        foreach ($brands as &$brand) {
            $brand['letter'] = $this->brandList->getLetter($brand['label']);
        }

        $configValues = $this->scopeConfig->getValue(
            BrandsWidget::CONFIG_VALUES_PATH,
            ScopeInterface::SCOPE_STORE
        );

        $data['items'] = $brands;
        $data['all_letters'] = implode(',', $this->brandList->getAllLetters());

        return array_merge($data, $configValues);
    }
}
