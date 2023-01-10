<?php
namespace Int\CustomerReviewsGraphQl\Model\Resolver;

use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\Resolver\ValueFactory;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\CatalogGraphQl\Model\Resolver\Product\Websites\Collection;

/**
 * Retrieves the Items information object
 */
class Items implements ResolverInterface
{
    /**
     * @inheritdoc
     */
    public function resolve(
        Field $field,
        $context,
        ResolveInfo $info,
        array $value = null,
        array $args = null
    ){
        if (!isset($value['rating'])) {
             return null;
        }

        $itemArray = [];
        
        foreach ($value['rating'] as $key => $item) {

            if($item['rating_id'] == 1){
                $itemArray['quality'] = $item['value'];
            }

            if($item['rating_id'] == 2){
                $itemArray['value'] = $item['value'];
            }

            if($item['rating_id'] == 3){
                $itemArray['price'] = $item['value'];
            }
            
        }
        
        return $itemArray;
    }
}
