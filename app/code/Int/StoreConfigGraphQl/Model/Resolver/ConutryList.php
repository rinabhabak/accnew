<?php
/**
 * Indusnet Technologies.
 *
 * @category  Indusnet
 * @package   Int_StoreConfigGraphQl
 * @author    Indusnet
 */

namespace Int\StoreConfigGraphQl\Model\Resolver;

use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Exception\GraphQlNoSuchEntityException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Catalog\Api\Data\ProductInterface;

/**
 * Class ConutryList
 * @package Int\StoreConfigGraphQl\Model\Resolver
 */
class ConutryList implements ResolverInterface
{


    private $_ratingDataProvider;
    protected $_countryCollectionFactory;
    private $_countryFactory;
    /**
     * @param \Magento\Directory\Model\ResourceModel\Country\CollectionFactory $countryCollectionFactory
     * @param \Magento\Directory\Model\CountryFactory $countryFactory
     */
    public function __construct(
        \Magento\Directory\Model\ResourceModel\Country\CollectionFactory $countryCollectionFactory,
        \Magento\Directory\Model\CountryFactory $countryFactory
    ) {
        $this->_countryCollectionFactory = $countryCollectionFactory;
        $this->_countryFactory = $countryFactory;
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
        
        $countryCollection = $this->_countryCollectionFactory->create()->loadByStore();
        $countryListArray = [];
            $i = 0;
            foreach ($countryCollection as $country) {  
            $countryListArray[$i]['country_code'] = $country['country_id'];
            $countryNameFactory = $this->_countryFactory->create()->loadByCode($country->getCountryId());
            $countryListArray[$i]['country_name'] = $countryNameFactory->getName();
            $i++;
        }
        return $countryListArray;

    }

}