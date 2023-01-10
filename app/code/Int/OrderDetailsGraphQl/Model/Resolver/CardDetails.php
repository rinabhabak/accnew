<?php
namespace Int\OrderDetailsGraphQl\Model\Resolver;

use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\Resolver\ValueFactory;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\CatalogGraphQl\Model\Resolver\Product\Websites\Collection;
/**
 * Retrieves the cardDetails information object
 */
class CardDetails implements ResolverInterface
{    

    public $CcType;

    public function __construct(
        
        \Magedelight\Cybersource\Model\Source\CcType $ccType

    ) {
         $this->CcType = $ccType;
    }
   
    /**
     * Get All Product Items of Order.
     * @inheritdoc
     */
    public function resolve(Field $field, $context, ResolveInfo $info, array $value = null, array $args = null)
    {
        
        if (!isset($value['card_details'])) {
             return null;
        }
        $cardValueArray = [];
        $cardFullName = '';
        $cctype_options = $this->CcType->toOptionArray();
        $cardValueArray[0]['method_title'] = $value['card_details']['method_title'];
        if(isset($value['card_details']['magedelight_cybersource_cards']) && count($value['card_details']['magedelight_cybersource_cards']) >0) {
        $get_card = $value['card_details']['magedelight_cybersource_cards'];
       
        foreach ($get_card as $key => $value) {
            $CardCode = $get_card[$key]['cc_type'];
            foreach ($cctype_options as $value) {
            
                if($value['value'] == $get_card[$key]['cc_type']){
                    $cardFullName = $value['label'];

                }
            }
            $cardValueArray[0]['cc_type_code'] = $get_card[$key]['cc_type'];
            $cardValueArray[0]['cc_type'] = $cardFullName;
            $cardValueArray[0]['cc_card_number'] = 'xxxx-'.$get_card[$key]['cc_last_4'];
            $cardValueArray[0]['processed_amount'] = $get_card[$key]['processed_amount'];
        }
    }
        return $cardValueArray;
    }
}
