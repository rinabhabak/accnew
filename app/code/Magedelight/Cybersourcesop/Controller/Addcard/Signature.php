<?php
/**
* Magedelight
* Copyright (C) 2017 Magedelight <info@magedelight.com>
*
* NOTICE OF LICENSE
*
* This program is free software: you can redistribute it and/or modify
* it under the terms of the GNU General Public License as published by
* the Free Software Foundation, either version 3 of the License, or
* (at your option) any later version.
*
* This program is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
* GNU General Public License for more details.
*
* You should have received a copy of the GNU General Public License
* along with this program. If not, see http://opensource.org/licenses/gpl-3.0.html.
*
* @category Magedelight
* @package Magedelight_Cybersourcedc
* @copyright Copyright (c) 2017 Mage Delight (http://www.magedelight.com/)
* @license http://opensource.org/licenses/gpl-3.0.html GNU General Public License,version 3 (GPL-3.0)
* @author Magedelight <info@magedelight.com>
*/
namespace Magedelight\Cybersourcesop\Controller\Addcard;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\Controller\ResultFactory;
use Magento\Directory\Model\Region;

class Signature extends Action {

    /*define ('HMAC_SHA256', 'sha256');*/

    static private $ccTypeMap = [
                                    'AE' => '003',
                                    'VI' => '001',
                                    'MC' => '002',
                                    'DI' => '004',
                                    'DN' => '005',
                                    'JCB' => '007',
                                    'MD' => '024',
                                    'MI' => '042'
                                ];

    static private $signedFieldNames = [
                                            'access_key',
                                            'profile_id',
                                            'transaction_uuid',
                                            'signed_field_names',
                                            'unsigned_field_names',
                                            'signed_date_time',
                                            'locale','transaction_type',
                                            'reference_number',
                                            'currency',
                                            'payment_method',
                                            'payment_token',
                                            'allow_payment_token_update',
                                            'bill_to_forename',
                                            'bill_to_surname',
                                            'bill_to_email',
                                            'bill_to_company_name',
                                            'bill_to_phone',
                                            'bill_to_address_line1',
                                            'bill_to_address_city',
                                            'bill_to_address_state',
                                            'bill_to_address_country',
                                            'bill_to_address_postal_code'
                                        ];

    public function __construct(ScopeConfigInterface $scopeConfig, PageFactory $pageFactory, Region $region,Context $context) {
        $this->scopeConfig = $scopeConfig;
        $this->pageFactory = $pageFactory;
        $this->region = $region;
        parent::__construct($context);
    }

    /**
     * 
     * @return Array
     */
    public function execute() {
        $requestParams = $this->getRequest()->getParams();
        $cctype = $this->getRequest()->getParam('cardtype');

        $params = [];
        
        if (isset($requestParams['regiod_id'])) {
            $state = $this->getBillToAddressState($requestParams['regiod_id'], $requestParams['country_id']);
        }else{
            $state = $requestParams['state'];
        }

        $requestParams['bill_to_address_state'] = $state;
        $requestParams['bill_to_address_country'] = $requestParams['country_id'];
        
        foreach($requestParams as $name => $value) {
            if (in_array($name, self::$signedFieldNames)) {
                $params[$name] = $value;
            }
        }
        
        $response = [];
        $response['signature'] = $this->sign($params);
        if (!empty($cctype)) {
            $response['cc_type'] = $this->getCcType($cctype);
            $response['expiration_date'] = $this->getExpirationDate($requestParams['cc_exp_month'],$requestParams['cc_exp_year']);    
        }
        
        
        if (isset($requestParams['regiod_id'])) {
            $response['bill_to_address_state'] = $this->getBillToAddressState($requestParams['regiod_id'], $requestParams['country_id']);
        }else{
            $response['bill_to_address_state'] = $requestParams['state'];
        }

        
        $response['country_id'] = $requestParams['country_id'];
                
        $resultJson = $this->resultFactory->create(ResultFactory::TYPE_JSON);
        $resultJson->setData($response);
        return $resultJson;
    }

    public function sign($params) {
      return $this->signData($this->buildDataToSign($params), $this->getSecretKey());
    }

    public function signData($data, $secretKey) {
        return base64_encode(hash_hmac('sha256', $data, $secretKey, true));
    }

    public function buildDataToSign($params) {
            $signedFieldNames = explode(",",$params["signed_field_names"]);
            foreach ($signedFieldNames as $field) {
               $dataToSign[] = $field . "=" . $params[$field];
            }
            return $this->commaSeparate($dataToSign);
    }

    public function commaSeparate ($dataToSign) {
        return implode(",",$dataToSign);
    }


    public function getCcType($cctype) {
        return self::$ccTypeMap[$cctype];
    }

    public function getExpirationDate($month,$year) {
        $month = sprintf("%02d", $month);
        return $month.'-'.$year;
    }

    public function getBillToAddressState($regionId,$countryId) {
        
        $addressState = $this->region->load($regionId);
        if ($countryId == 'CA' || $countryId == 'US') {
            return $addressState->getCode();
        }else{
            return $addressState->getName();
        }
    }

    public function getSecretKey()
    {
        return $this->scopeConfig->getValue('payment/cybersourcesop/secret_key', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

}
