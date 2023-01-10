<?php
namespace Int\OrderDetailsGraphQl\Model\Resolver;

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

    protected $_productloader;  


    public function __construct(
        
        \Magento\Catalog\Model\ProductFactory $_productloader

    ) {

        $this->_productloader = $_productloader;
       
    }
    public function getLoadProduct($id)
    {
        return $this->_productloader->create()->load($id);
    }

    /**
     * Get All Product Items of Order.
     * @inheritdoc
     */
    public function resolve(Field $field, $context, ResolveInfo $info, array $value = null, array $args = null)
    {
        if (!isset($value['items'])) {
             return null;
        }
        $itemArray = [];

       
        $configurable_attribute = [];
        $configurable_attribute_value = null;

        
        foreach ($value['items'] as $key => $item) {

            $product=$this->getLoadProduct($item['product_id']);
            $parent_sku = '';
            $i=0;
            if($item['product_type'] == 'configurable')
            {
                $parent_sku = $product->getSku();
                $optionArray=[];
                $configurable_attributes = $item['product_options']['attributes_info'][0];
                
                foreach($item['product_options']['attributes_info'] as $attributes_info){
                    $itemArray[$key]['productoptions'][$i]['label'] = $attributes_info['label'];
                    $itemArray[$key]['productoptions'][$i]['value'] = $attributes_info['value'];
                    $i++;
                }
                
                
            }
            
            $itemArray[$key]['parent_sku'] = $parent_sku;
            $itemArray[$key]['sku'] = $item['sku'];
            $itemArray[$key]['title'] = $item['name'];
            $itemArray[$key]['price'] = $item['price'];
            $itemArray[$key]['base_price'] = $item['base_price'];
            $itemArray[$key]['row_total'] = $item['row_total'];
            $itemArray[$key]['base_row_total'] = $item['base_row_total'];
            $itemArray[$key]['qty'] = round($item['qty_ordered']);
            $itemArray[$key]['product_type'] = $item['product_type'];
            $itemArray[$key]['product_id'] = $item['product_id'];
            //$itemArray[$key]['label'] = $configurable_attribute_label;
            //$itemArray[$key]['value'] = $configurable_attribute_value;
        }

        return $itemArray;
    }
}
