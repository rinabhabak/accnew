<?php
/**
 * Indusnet Technologies.
 *
 * @category  Indusnet
 * @package   Int_ConfiguratorGraphQl
 * @author    Indusnet
 */

namespace Int\ConfiguratorGraphQl\Model\Resolver;

use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\Resolver\ValueFactory;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\CatalogGraphQl\Model\Resolver\Product\Websites\Collection;

/**
 * Class Items
 * @package Int\ConfiguratorGraphQl\Model\Resolver
 */
class Items implements ResolverInterface
{
    protected $_productloader;
    protected $_storeManager;

    public function __construct(
        \Magento\Catalog\Model\ProductFactory $_productloader,
        \Magento\Store\Model\StoreManagerInterface $storemanager
    ) {
        $this->_productloader = $_productloader;
        $this->_storeManager =  $storemanager;
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
        if (!isset($value['items']['configurator_pid'])) {
             return null;
        }

        if (!isset($value['items']['order_items'])) {
             return null;
        }

        $configuratorPid = $value['items']['configurator_pid'];

        $itemArray = [];

        $configurable_attribute = [];
        $configurable_attribute_value = null;

        foreach ($value['items']['order_items'] as $key => $item) {
            if($item['configurator_pid'] == $configuratorPid) {
                $product=$this->getLoadProduct($item['product_id']);
                
                $attr = $product->getResource()->getAttribute('length');
                $optionId = $product->getLength();

                if ($attr->usesSource()) {
                    $productLength = $attr->getSource()->getOptionText($optionId);
                } else {
                    $productLength = null;
                }
                
                $attr = $product->getResource()->getAttribute('system_compatibility');
                $systemCompatibilityOptionIds = $product->getSystemCompatibility();
                $systemCompatibilityInfo = explode(',', $systemCompatibilityOptionIds);

                $systemCompatibility = null;
                if ($attr->usesSource()) {
                    foreach($systemCompatibilityInfo as $value) {
                            $optionId = trim($value);
                            $systemCompatibility .= $attr->getSource()->getOptionText($optionId).', ';
                    }
                } else {
                    $systemCompatibility = null;
                }

                $systemCompatibility = substr($systemCompatibility, 0, -2);

                if(empty($systemCompatibility)) {
                    $systemCompatibility = null;
                }

                $productForSales = $product->getProductForSales();

                $store = $this->_storeManager->getStore();
                $productImageUrl = $store->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA) . 'catalog/product' .$product->getImage();
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
                $itemArray[$key]['configurator_pid'] = $item['configurator_pid'];
                $itemArray[$key]['title'] = $item['name'];
                $itemArray[$key]['image'] = $productImageUrl;
                $itemArray[$key]['length'] = $productLength;
                $itemArray[$key]['system_compatibility'] = $systemCompatibility;
                $itemArray[$key]['is_salable'] = $productForSales;
                $itemArray[$key]['price'] = $item['price'];
                $itemArray[$key]['qty'] = round($item['qty_ordered']);
                $itemArray[$key]['product_type'] = $item['product_type'];
                $itemArray[$key]['product_id'] = $item['product_id'];
            }
        }

        return $itemArray;
    }
}