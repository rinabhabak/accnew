<?php
namespace Int\OrderDetailsGraphQl\Model\Resolver;

use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\Resolver\ValueFactory;
use Magento\Framework\GraphQl\Query\ResolverInterface;

/**
 * Retrieves the Shipping information object
 */
class Shipping implements ResolverInterface
{
    /**
     * @inheritdoc
     */
    public function resolve(Field $field, $context, ResolveInfo $info, array $value = null, array $args = null)
    {
        if (!isset($value['shipping_address'])) {
             return null;
        }
        
        $shippingData = $value['shipping_address'];
        $shipping_array = explode(PHP_EOL, $shippingData['street']);
        $street_str = '';
        if(count($shipping_array))
        $street_str = implode(' ',$shipping_array);
        $shippingAddress = [];
        $shippingAddress['shipping']['name'] = $shippingData['firstname'].' '.$shippingData['lastname'];
        $shippingAddress['shipping']['street'] = trim(preg_replace('/\s+/', ' ', $street_str));
        //$shippingAddress['shipping']['street'] = count($shippingData['street']) > 1 ? implode(" , ",$shippingData['street']) : $shippingData['street'];
        $shippingAddress['shipping']['city'] = $shippingData['city'];
        $shippingAddress['shipping']['region'] = $shippingData['region'];
        $shippingAddress['shipping']['country_id'] = $shippingData['country_id'];
        $shippingAddress['shipping']['postcode'] = $shippingData['postcode'];
        $shippingAddress['shipping']['telephone'] = $shippingData['telephone'];
        $shippingAddress['shipping']['fax'] = $shippingData['fax'];
        $shippingAddress['shipping']['company'] = $shippingData['company'];
        
        return $shippingAddress;
    }
}
