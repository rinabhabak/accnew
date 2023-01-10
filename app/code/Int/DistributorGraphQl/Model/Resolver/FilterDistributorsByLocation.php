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
use Alpine\Storelocator\Model\Attribute;
use Amasty\Storelocator\Controller\Index\Ajax as BaseAjax;
use Amasty\Storelocator\Helper\Data;



/**
 * Class FilterDistributorsByLocation
 * @package Int\DistributorGraphQl\Model\Resolver
 */
class FilterDistributorsByLocation implements ResolverInterface
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

    /**
     * Helper
     *
     * @var Data
     */
    protected $dataHelper;

    /**
     * Attribute model
     *
     * @var Attribute
     */
    protected $attributeModel;

     public function __construct(
        \Amasty\Storelocator\Model\ResourceModel\Attribute\Collection $attributeCollection,
        \Amasty\Base\Model\Serializer $serializer,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Amasty\Storelocator\Model\ResourceModel\Location\Collection $locationCollection,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\View\Element\BlockFactory $blockFactory,
        Data $dataHelper,
        Attribute $attributeModel
    ) {
        $this->attributeCollection = $attributeCollection;
        $this->serializer = $serializer;
        $this->_storeManager = $storeManager;
        $this->locationCollection = $locationCollection;
        $this->registry = $registry;
        $this->_blockFactory = $blockFactory;
        $this->dataHelper = $dataHelper;
        $this->attributeModel = $attributeModel;
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
            $OptionValue = $args['optionValue'];
            parse_str($OptionValue, $output);


            $lat = $output['lat'];
            $lng = $output['lng'];
            $radius = $output['radius'];
            $locationCollection->applyDefaultFiltersLocation($lat,$lng,$radius);



            $product = false;
            if (array_key_exists("product",$output)){
                $productId = $output['product'];

                if ($productId) {
                    $product = $this->_objectManager->get('Magento\Catalog\Model\Product')->load($productId);
                }
            }


            if ($industry = $output['industry']) {
                $data = [
                    $this->attributeModel->getAttributeIdByCode(Attribute::INDUSTRY_CODE) => $industry
                ];

                $locationCollection->applyAttributeFilters($data);
            }

            $locationCollection->load();
            $locationCollection->getSelect()->assemble();
            $locationCollection->getSelect()->__toString();

            $this->registry->register('amlocator_location', $locationCollection);


            $arrayCollection = [];
            $i = 0;
            foreach ($locationCollection as $item) {
                if ($product) {
                    $valid = $this->dataHelper->validateLocation($item, $product);
                    if (!$valid) {
                        continue;
                    }
                }
                $arrayCollection[0]['items'][$i] = $item->getData();
                $i++;
            }

            $arrayCollection[0]['totalRecords'] = isset($arrayCollection[0]['items']) ? count($arrayCollection[0]['items']) : 0;

            $count = 0;
            $childCount = 0;

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