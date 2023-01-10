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

class Setting implements ResolverInterface
{
    /**
     * @var \Amasty\ShopbyBrand\Helper\Data
     */
    private $settings;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    public function __construct(
        \Amasty\ShopbyBrand\Helper\Data $settings,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    ) {
        $this->settings = $settings;
        $this->storeManager = $storeManager;
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
        try {
            if (isset($args['storeId'])) {
                $this->storeManager->setCurrentStore($args['storeId']);
            }

            return $this->getData();
        } catch (\Exception $e) {
            return ['error' => __('Wrong post id.')];
        }
    }

    /**
     * @return array
     */
    private function getData()
    {
        return [
            'topmenu_enabled' => $this->settings->isTopmenuEnabled(),
            'menu_item_label' => $this->settings->getBrandLabel(),
            'product_page_width' => $this->settings->getLogoProductPageWidth(),
            'product_page_height' => $this->settings->getLogoProductPageHeight(),
            'listing_brand_logo_width' => $this->settings->getBrandLogoProductListingWidth(),
            'listing_brand_logo_height' => $this->settings->getBrandLogoProductListingHeight(),
        ];
    }
}
