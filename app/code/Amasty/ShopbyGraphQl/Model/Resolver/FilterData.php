<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_ShopbyGraphQl
 */


namespace Amasty\ShopbyGraphQl\Model\Resolver;

use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\Resolver\ValueFactory;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Amasty\ShopbyBase\Model\ResourceModel\FilterSetting\CollectionExtended;
use Amasty\Shopby\Helper\FilterSetting;
use Amasty\Shopby\Helper\Category;

class FilterData implements ResolverInterface
{
    const CATEGORY_ID = 'category_id';

    /**
     * @var ValueFactory
     */
    private $valueFactory;

    /**
     * @var CollectionExtended
     */
    private $filterSetting;

    public function __construct(
        ValueFactory $valueFactory,
        CollectionExtended $filterSetting
    ) {
        $this->valueFactory = $valueFactory;
        $this->filterSetting = $filterSetting;
    }

    /**
     * @param Field $field
     * @param \Magento\Framework\GraphQl\Query\Resolver\ContextInterface $context
     * @param ResolveInfo $info
     * @param array|null $value
     * @param array|null $args
     * @return \Magento\Framework\GraphQl\Query\Resolver\Value|mixed
     */
    public function resolve(Field $field, $context, ResolveInfo $info, array $value = null, array $args = null)
    {
        try {
            $attributeCode = $value['attribute_code'] == self::CATEGORY_ID
                ? Category::ATTRIBUTE_CODE
                : $value['attribute_code'];
            $filterSetting = $this->filterSetting->getItemByCode(FilterSetting::ATTR_PREFIX . $attributeCode);
            $data = $filterSetting->getData();
        } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
            $data = [];
        }

        $result = function () use ($data) {
            return $data;
        };

        return $this->valueFactory->create($result);
    }
}
