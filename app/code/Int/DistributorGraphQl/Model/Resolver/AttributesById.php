<?php
/**
 * Indusnet Technologies.
 *
 * @category  Indusnet
 * @package   Int_DistributorGraphQl
 * @author    Indusnet
 */

namespace Int\DistributorGraphQl\Model\Resolver;

use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Exception\GraphQlNoSuchEntityException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\CustomerGraphQl\Model\Customer\GetCustomer;
use Magento\Framework\GraphQl\Exception\GraphQlAuthorizationException;



/**
 * Class AttributesById
 * @package Int\DistributorGraphQl\Model\Resolver
 */
class AttributesById implements ResolverInterface
{

    /**
     * @var \Amasty\Storelocator\Model\ResourceModel\Attribute\Collection
     */
    protected $attributeCollection;

    /**
     * @var \Amasty\Base\Model\Serializer
     */
    protected $serializer;

    protected $_storeManager;

     public function __construct(
        \Amasty\Storelocator\Model\ResourceModel\Attribute\Collection $attributeCollection,
        \Amasty\Base\Model\Serializer $serializer,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    ) {
        $this->attributeCollection = $attributeCollection;
        $this->serializer = $serializer;
        $this->_storeManager = $storeManager;
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
        try {
            if (!isset($args['attributeId'])) {
                throw new GraphQlInputException(__('Attribute ID should be specified'));
            }
            $AttributeIds = $args['attributeId'];
            $_attributes = array();
            $attributeCollection = $this->getAttributes($AttributeIds);
            $i = 0;


            $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/dtrbtr.log');
            $logger = new \Zend\Log\Logger();
            $logger->addWriter($writer);
            $logger->info($AttributeIds);
            foreach($attributeCollection as $attributes):
                $_attributes[$i]['attribute_id'] = $attributes['attribute_id'];
                $_attributes[$i]['label'] = $attributes['label'];

                $j = 0;
                foreach($attributes['options'] as $valueId => $option):
                    $_attributes[$i]['options'][$j]['value'] = $valueId;
                    $_attributes[$i]['options'][$j]['label'] = $option;
                    $j++;
                endforeach;
                $i++;
            endforeach;
        }catch(\Exception $e) {
            throw new GraphQlInputException(__($e->getMessage()));
        }
        return $_attributes;
    }

    public function getAttributes($AttributeIds)
    {
        $types = array('select','multiselect','boolean','text');
        $collection = $this->attributeCollection
            ->joinAttributes($types);
        $attrAsArray = $collection->getAttributes();

        $storeId = $this->_storeManager->getStore(true)->getId();

        $attributes = [];

        foreach ($attrAsArray as $attribute) {
            if($AttributeIds == $attribute['attribute_id']){
                $attributeId = $attribute['attribute_id'];
                if (!array_key_exists($attributeId, $attributes)) {
                    $attrLabel = $attribute['frontend_label'];
                    $labels = $this->serializer->unserialize($attribute['label_serialized']);
                    if (isset($labels[$storeId]) && $labels[$storeId]) {
                        $attrLabel = $labels[$storeId];
                    }
                    $attributes[$attributeId] = [
                        'attribute_id' => $attributeId,
                        'label' => $attrLabel,
                        'options' => [],
                        'frontend_input' => $attribute['frontend_input']
                    ];
                }

                if ($attribute['frontend_input'] == 'boolean') {
                    $attributes[$attributeId]['options'][0] = __('No');
                    $attributes[$attributeId]['options'][1] = __('Yes');
                } else {
                    $options = $this->serializer->unserialize($attribute['options_serialized']);
                    $optionLabel = $options[0];
                    if (isset($options[$storeId]) && $options[$storeId]) {
                        $optionLabel = $options[$storeId];
                    }
                    $attributes[$attributeId]['options'][$attribute['value_id']] = $optionLabel;
                }
            }
        }

        return $attributes;
    }

}