<?php
namespace Int\OrderDetailsGraphQl\Model\Resolver;

use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\Resolver\ValueFactory;
use Magento\Framework\GraphQl\Query\ResolverInterface;

/**
 * Retrieves the Billing information object
 */
class Billing implements ResolverInterface
{
    /**
     * @inheritdoc
     */
    public function resolve(Field $field, $context, ResolveInfo $info, array $value = null, array $args = null)
    {
        if (!isset($value['billing_address'])) {
             return null;
        }
        
        $billingData = $value['billing_address'];
        $billing_array = explode(PHP_EOL, $billingData['street']);
        $street_str = '';
        if(count($billing_array))
        $street_str = implode(' ',$billing_array);
        $billingAddress = [];
        $billingAddress['billing']['name'] = $billingData['firstname'].' '.$billingData['lastname'];
        //$billingAddress['billing']['street'] = count($billingData['street']) > 1 ? implode(" , ",$billingData['street']) : $billingData['street'];
        $billingAddress['billing']['street'] = trim(preg_replace('/\s+/', ' ', $street_str));
        $billingAddress['billing']['city'] = $billingData['city'];
        $billingAddress['billing']['region'] = $billingData['region'];
        $billingAddress['billing']['country_id'] = $billingData['country_id'];
        $billingAddress['billing']['postcode'] = $billingData['postcode'];
        $billingAddress['billing']['telephone'] = $billingData['telephone'];
        $billingAddress['billing']['fax'] = $billingData['fax'];
        $billingAddress['billing']['company'] = $billingData['company'];
        
        return $billingAddress;
    }
}
