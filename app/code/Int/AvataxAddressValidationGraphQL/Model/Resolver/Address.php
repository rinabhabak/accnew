<?php
/**
 * Copyright © Indus Net Technologies All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Int\AvataxAddressValidationGraphQL\Model\Resolver;

use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Exception\GraphQlNoSuchEntityException;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Framework\GraphQl\Exception\GraphQlAuthorizationException;
use Magento\Customer\Api\Data\AddressInterface;

class Address implements ResolverInterface
{
    /**
     * Store manager
     *
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \ClassyLlama\AvaTax\Api\ValidAddressManagementInterface
     */
    protected $validateAddress;

    /**
     * @var \Magento\Customer\Api\Data\AddressInterfaceFactory
     */
    private $customerAddressFactory = null;

    /**
     * @var \Magento\Framework\Api\DataObjectHelper
     */
    protected $dataObjectHelper = null;

    /**
     * @var \Magento\Directory\Model\RegionFactory
     */
    protected $_regionFactory = null;

    /**
     * @var const VALIDATE
     */
    protected const VALIDATE = 'validate';
    
    
    public function __construct(
        \ClassyLlama\AvaTax\Api\ValidAddressManagementInterface $validateAddress,
        \Magento\Customer\Api\Data\AddressInterfaceFactory $customerAddressFactory,
        \Magento\Framework\Api\DataObjectHelper $dataObjectHelper,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Directory\Model\RegionFactory $regionFactory
    ){
        $this->storeManager = $storeManager;
        $this->validateAddress = $validateAddress;
        $this->customerAddressFactory = $customerAddressFactory;
        $this->dataObjectHelper = $dataObjectHelper;
        $this->_regionFactory = $regionFactory;
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

        if(empty($args) || !is_array($args)) {
            throw new GraphQlInputException(__('"address" value should be specified'));
        }

        try {
            $customerAddressData = (array) $args;
            $customerAddressDataWithRegion = [];

            $customerAddressDataWithRegion['region']['region'] = $args['region'];
            if (isset($args['region_id'])) {
                $customerAddressDataWithRegion['region']['region_id'] = $args['region_id'];
            }
            if(isset($args['region_code'])) {
                $customerAddressDataWithRegion['region']['region_code'] = $args['region_code'];
            }
            if(empty($args['region_id']) && !empty($args['region_code']) && !empty($args['country_id'])){
                $region_id = $this->getRegionIdByCode($args['region_code'], $args['country_id']);
                $customerAddressDataWithRegion['region_id'] = $region_id;
                $customerAddressDataWithRegion['region']['region_id'] = $region_id;
            }
            
            $customerAddressData = array_merge($customerAddressData, $customerAddressDataWithRegion);

            $addressDataObject = $this->customerAddressFactory->create();

            $this->dataObjectHelper->populateWithArray(
                $addressDataObject,
                $customerAddressData,
                '\Magento\Customer\Api\Data\AddressInterface'
            );

            $validate_response = $this->validateAddress->saveValidAddress(
                $addressDataObject, 
                $this->storeManager->getStore()->getId()
            );

            if(is_string($validate_response) === true){
                throw new GraphQlInputException(__('Provided address is incorrect. Please provide valid address.'));
            }
            
            return [
                AddressInterface::FIRSTNAME => $validate_response->getFirstname(),
                AddressInterface::LASTNAME => $validate_response->getLastname(),
                AddressInterface::STREET => $validate_response->getStreet(),
                AddressInterface::COUNTRY_ID => $validate_response->getCountryId(),
                AddressInterface::CITY => $validate_response->getCity(),
                AddressInterface::REGION_ID => $validate_response->getRegionId(),
                AddressInterface::REGION => $validate_response->getRegion(),
                AddressInterface::POSTCODE => $validate_response->getPostcode(),
                self::VALIDATE => $this->checkSimilarity($customerAddressData, $validate_response)
            ];

        } catch (\Exception $e) {
            throw new GraphQlInputException(__($e->getMessage()));
        }
        
    }

    /**
    * @param $request
    * @param $response
    * @return boolen
    */
    protected function checkSimilarity($request, $response)
    {
    	    

            $status = false;

            if($request['country_id'] !== $response->getCountryId()){
            	$status = true;
            }

            if($request['city'] !== $response->getCity()){
            	$status = true;
            }

            if($request['postcode'] !== $response->getPostcode()){
            	$status = true;
            }

            if($request['street'] !== $response->getStreet()){
            	$status = true;
            }

            if($request['region']['region'] !== $response->getRegion()){
            	$status = true;
            }

            return $status;
    }

    /**
    * @param $region_code
    * @param $country_id
    * @return string
    */
    protected function getRegionIdByCode($region_code, $country_id)
    {
        try {
            $region   = $this->_regionFactory->create();
            $regionData = $region->loadByCode($region_code, $country_id);
            return $regionData->getRegionId();
        }
        catch(\Exception $e) {
            throw new GraphQlInputException(__($e->getMessage()));
            return false;
        }
    }

}

