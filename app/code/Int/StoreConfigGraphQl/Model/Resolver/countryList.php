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
 * Class countryList
 * @package Int\StoreConfigGraphQl\Model\Resolver
 */
class countryList implements ResolverInterface
{


    private $_ratingDataProvider;
    protected $_countryCollectionFactory;
    private $_countryFactory;
    protected $_scopeConfig;

    /**
     * @param \Magento\Directory\Model\ResourceModel\Country\CollectionFactory $countryCollectionFactory
     * @param \Magento\Directory\Model\CountryFactory $countryFactory
     */
    public function __construct(
        \Magento\Directory\Model\ResourceModel\Country\CollectionFactory $countryCollectionFactory,
        \Magento\Directory\Model\CountryFactory $countryFactory,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeInterface
    ) {
        $this->_countryCollectionFactory = $countryCollectionFactory;
        $this->_countryFactory = $countryFactory;
        $this->_scopeConfig = $scopeInterface;
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
                $isDefault = '';
                $countryListArray[$i]['country_code'] = $country['country_id'];
                $countryNameFactory = $this->_countryFactory->create()->loadByCode($country->getCountryId());
                $countryListArray[$i]['country_name'] = $countryNameFactory->getName();
                $defaultCountry = $this->_scopeConfig->getValue('general/country/default', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
                if($country['country_id'] == $defaultCountry){
                    $isDefault = true;
                }else{
                    $isDefault = false;
                }
                $countryListArray[$i]['is_default'] = $isDefault;
                $i++;
        }
        return $countryListArray;

    }

}