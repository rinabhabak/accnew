<?php
/**
 * Indusnet Technologies.
 *
 * @category  Indusnet
 * @package   Int_AggregationGraphQl
 * @author    Indusnet
 */
declare(strict_types=1);

namespace Int\AggregationGraphQl\Model\Resolver\Product;

use Magento\CatalogGraphQl\Model\Resolver\Product\Price\Discount;
use Magento\CatalogGraphQl\Model\Resolver\Product\Price\ProviderPool as PriceProviderPool;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Catalog\Model\Product;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Pricing\SaleableInterface;
use Magento\Store\Api\Data\StoreInterface;

/**
 * Format product's QuantityIn information for uom field
 */
class QuantityIn implements ResolverInterface
{
   

    public function __construct(\Magento\Catalog\Model\ResourceModel\ProductFactory $productFactory)
    {
       $this->productFactory = $productFactory;
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
        if (!isset($value['model'])) {
            throw new LocalizedException(__('"model" value should be specified'));
        }
        /** @var StoreInterface $store */
        $store = $context->getExtensionAttributes()->getStore();

        /** @var Product $product */
        $product = $value['model'];
        $option_Text = '';

        $poductReource=$this->productFactory->create();
        $attribute = $poductReource->getAttribute('uom');
        if ($attribute->usesSource()) {
            if(isset($value['uom'])){
                $option_Text = $attribute->getSource()->getOptionText($value['uom']);
            }

            
        }

        return $option_Text;
    }

    
}
