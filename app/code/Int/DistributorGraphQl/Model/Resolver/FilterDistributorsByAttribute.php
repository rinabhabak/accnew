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
 * Class FilterDistributorsByAttribute
 * @package Int\DistributorGraphQl\Model\Resolver
 */
class FilterDistributorsByAttribute implements ResolverInterface
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

    /**
     * @var \Amasty\Storelocator\Model\ResourceModel\Location\Collection
     */
    protected $locationCollection;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    protected $_blockFactory;

     public function __construct(
        \Amasty\Storelocator\Model\ResourceModel\Attribute\Collection $attributeCollection,
        \Amasty\Base\Model\Serializer $serializer,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Amasty\Storelocator\Model\ResourceModel\Location\Collection $locationCollection,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\View\Element\BlockFactory $blockFactory
    ) {
        $this->attributeCollection = $attributeCollection;
        $this->serializer = $serializer;
        $this->_storeManager = $storeManager;
        $this->locationCollection = $locationCollection;
        $this->registry = $registry;
        $this->_blockFactory = $blockFactory;
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
            $locationCollection = $this->locationCollection;
            $locationCollection->setOrder('name', 'ASC');
            $OptionValue = $args['optionValue'];
            parse_str($OptionValue, $attributes);
            $locationCollection->applyDefaultFilters($attributes['radius']);

            $result = array();
            if (isset($OptionValue)) {
                parse_str($OptionValue, $attributes);
                if (isset($attributes['attribute_id'])
                    && !empty($attributes['attribute_id'])
                    && isset($attributes['option'])
                    && !empty($attributes['option'])
                ) {
                    foreach ($attributes['attribute_id'] as $attributeId) {
                        if (isset($attributes['option'][$attributeId]) && $attributes['option'][$attributeId] != '') {
                            $result[(int)$attributeId] = (int)$attributes['option'][$attributeId];
                        }
                    }
                }
            }

            if (count($result)) {
                $locationCollection->applyAttributeFilters($result);
            }

            $this->registry->register('amlocator_location', $locationCollection);
            $arrayCollection = [];
            $i = 0;
            if ($locationCollection->count()) {
                foreach ($locationCollection as $item) {
                    $item->load($item->getId());
                    $arrayCollection[0]['items'][$i] = $item->getData();
                    $i++;
                }
            }
            else {
                throw new GraphQlInputException(__('Locations have not been found'));
            }

            $arrayCollection[0]['totalRecords'] = isset($arrayCollection[0]['items']) ? count($arrayCollection[0]['items']) : 0;

            $count = 0;
            $childCount = 0;
            // foreach($arrayCollection[0]['items'] as $_items){
                
            //         foreach($_items['attributes'] as $_attribues){
            //             if(ISSET($_items['attributes'])){
            //                 $arrayCollection[0]['items'][$count]['attributes'][$childCount]['option_title'][0]['title'] = $_attribues['option_title'][0][0];
            //                 $arrayCollection[0]['items'][$count]['attributes'][$childCount]['option_title'][0]['value'] = $_attribues['option_title'][0][1];
            //                 $childCount++;
            //             }
            //         }
            //         $count++;
            // }

        }catch(\Exception $e) {
            throw new GraphQlInputException(__($e->getMessage()));
        }
        return $arrayCollection;
    }

    public function getAttributes($AttributeIds)
    {
        $collection = $this->attributeCollection
            ->joinAttributes();
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