<?php
/**
 * Magedelight
 * Copyright (C) 2019 Magedelight <info@magedelight.com>
 *
 * @category Magedelight
 * @package Magedelight_Cybersource
 * @copyright Copyright (c) 2019 Mage Delight (http://www.magedelight.com/)
 * @license http://opensource.org/licenses/gpl-3.0.html GNU General Public License,version 3 (GPL-3.0)
 * @author Magedelight <info@magedelight.com>
 */
namespace Magedelight\Cybersource\Model\Api;

use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magedelight\Cybersource\Api\Data\CardManageInterface;
use Magedelight\Cybersource\Api\CybersourceTokenRepositoryInterface;

class CardManagement implements \Magedelight\Cybersource\Api\CardManagementInterface
{
    const SEVERE_ERROR = 0;
    const SUCCESS = 1;
    const LOCAL_ERROR = 2;

    /**
     *
     * @var Magedelight\Cybersource\Model\CardsFactory 
     */
    protected $cardsFactory;

    /**
     * @var \Magento\Framework\Api\FilterBuilder
     */
    protected $filterBuilder;
    
    /**
     * @var \Magento\Framework\Api\SearchCriteriaBuilder
     */
    protected $searchCriteriaBuilder;
    
    protected $cybersourceHelper;
    
     /**
     * Cybersource api model.
     *
     * @var \Magedelight\Cybersource\Model\Api\Soap
     */
    protected $soapModel;
    
    /**
     *
     * @var Magento\Framework\DataObject 
     */
    protected $requestObject;

    /**
     *
     * @var Magento\Customer\Api\CustomerRepositoryInterface 
     */
    protected $customerRepository;
    
    /**
     * 
     * @param CybersourceTokenRepositoryInterface $repository
     * @param FilterBuilder $filterBuilder
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param \Magedelight\Cybersource\Model\CardsFactory $cardsFactory
     * @param \Magedelight\Cybersource\Model\Api\Soap $soapModel
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magedelight\Cybersource\Helper\Data $cybersourceHelper
     * @param \Magento\Framework\DataObject $requestObject
     * @param \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository
     */
    public function __construct(
        CybersourceTokenRepositoryInterface $repository,    
        FilterBuilder $filterBuilder, 
        SearchCriteriaBuilder $searchCriteriaBuilder,    
        \Magedelight\Cybersource\Model\CardsFactory $cardsFactory,
        \Magedelight\Cybersource\Model\Api\Soap $soapModel,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magedelight\Cybersource\Helper\Data $cybersourceHelper,
        \Magento\Framework\DataObject $requestObject,
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository    
     ) {
        $this->cybersourceTokenRepository = $repository;
        $this->filterBuilder = $filterBuilder;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->cardsFactory = $cardsFactory;
        $this->soapModel = $soapModel;
        $this->storeManager = $storeManager;
        $this->cybersourceHelper = $cybersourceHelper;
        $this->requestObject = $requestObject;
        $this->customerRepository = $customerRepository;
    }

    /**
     * get Customer Card Data.
     *
     * @api
     *
     * @param int $customerId
     *
     * @return \Magedelight\Cybersource\Api\Data\CardManageInterface[] Cybersource tokens search result interface.
     */
    public function getCardListing($customerId)
    {
        try {
             $filters[] = $this->filterBuilder
            ->setField(CardManageInterface::CUSTOMER_ID)
            ->setValue($customerId)
            ->create();
            $entities = $this->cybersourceTokenRepository->getList(
                $this->searchCriteriaBuilder
                    ->addFilters($filters)
                    ->create()
            )->getItems();
    
            return $entities;
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $returnArray['error'] = $e->getMessage();
            $returnArray['status'] = 0;
            $errormsg[] = $returnArray;
            return $errormsg;
        } catch (\Exception $e) {
            $returnArray['error'] = __('unable to process request');
            $returnArray['status'] = 2;
            $errormsg[] = $returnArray;
            return $errormsg;
        }
    }
    
    /**
     * add customer card
     *
     * @api
     *
     * @param int $customerid
     * @param \Magedelight\Cybersource\Api\Data\CardManageInterface $cybersourcecard
     *
     * @return \Magedelight\Cybersource\Api\Data\CardManageInterface[] Cybersource tokens add card result interface.
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function addCustomerCard($customerid,\Magedelight\Cybersource\Api\Data\CardManageInterface $cybersourcecard)
    {
        try {
            $params = $cybersourcecard->getData();
            $requestObject = $this->requestObject;
            $customer = $this->customerRepository->getById($customerid);
            $requestObject->addData(array(
                'customer_id' => $customerid,
                'email' => $customer->getEmail()
            ));
            
            $requestObject->addData($params);
            $response = $this->soapModel
            ->setInputData($requestObject)
            ->createCustomerProfile();
             $code = $response->reasonCode;
             if ($code == '100') {
                $subscriptionId = $response->paySubscriptionCreateReply->subscriptionID;
                if (!empty($subscriptionId)) {
                    $card = $this->cardsFactory->create();
                    $card->setFirstname($params['firstname']);
                    $card->setLastname($params['lastname']);
                    $card->setPostcode($params['postcode']);
                    $card->setCountryId($params['country_id']);
                    $card->setRegionId($params['region_id']);
                    $card->setState($params['state']);
                    $card->setCity($params['city']);
                    $card->setCompany($params['company']);
                    $card->setStreet($params['street']);
                    $card->setTelephone($params['telephone']);
                    $card->setCustomerId($customerid);
                    $card->setSubscriptionId($subscriptionId);
                    $card->setWebsiteId($params['website_id']);        
                    $card->setccType($params['cc_type']);
                    $card->setcc_exp_month($params['cc_exp_month']);
                    $card->setcc_exp_year($params['cc_exp_year']);
                    $card->setCcLast4(substr($params['cc_number'], -4, 4));
                    $card->setCreatedAt(date('Y-m-d H:i:s'));
                    $card->setUpdatedAt(date('Y-m-d H:i:s'));
                    $card->getResource()->save($card);
                    $returnArray['status'] = 1;
                    $returnArray['card'] = $card;
                    return $returnArray;
                }
            } else {
                $errorMessage = $this->cybersourceHelper->getErrorDescription($code);
                if ($code == '102' || $code == '101') {
                    $errorDescription = '';
                    if (isset($response->invalidField)) {
                        $errorDescription .= is_array($response->invalidField) ? implode(' ', $response->invalidField) : $response->invalidField;
                    }
                    if (isset($response->missingField)) {
                        $errorDescription .= is_array($response->missingField) ? implode(' ', $response->missingField) : $response->missingField;
                    }
                }
                if (isset($errorDescription) && !empty($errorDescription)) {
                    $message = __('Error code:') ." ".$code ." : " .$errorMessage. " : " . $errorDescription;
                    $returnArray['error'] = $message;
                    $returnArray['status'] = 0;
                    return $returnArray;
                } else {
                    $message = __('Error code:') ." ".$code ." : " .$errorMessage;
                    $returnArray['error'] = $message;
                    $returnArray['status'] = 0;
                    return $returnArray;
                }
            }
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $returnArray['error'] = $e->getMessage();
            $returnArray['status'] = 0;
            $errormsg[] = $returnArray;
            return $errormsg;
        } catch (\Exception $e) {
            $returnArray['error'] = __('unable to process request');
            $returnArray['status'] = 2;
            $errormsg[] = $returnArray;
            return $errormsg;
        }
    }
    
     /**
     * update customer card
     *
     * @api
     *
     * @param int $customerid
     * @param \Magedelight\Cybersource\Api\Data\CardManageInterface $cybersourcecard
     *
     * @return \Magedelight\Cybersource\Api\Data\CardManageInterface[] Cybersource tokens update card result interface.
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function updateCustomerCard($customerid,\Magedelight\Cybersource\Api\Data\CardManageInterface $cybersourcecard)
    {
        try {
            $params = $cybersourcecard->getData();
            $updateCardId = $params['card_id'];
            $card = $this->cybersourceTokenRepository->getById($updateCardId);
            if($card->getCustomerId() != $customerid){
                $returnArray['error'] = "Card Not Found";
                $returnArray['status'] = 0;
                return $returnArray;
            }
            $subscriptionId = $card->getData('subscription_id');
            $requestObject = $this->requestObject;
            $requestObject->addData(array(
                'customer_id' => $customerid,
                'customer_subscription_id' => $subscriptionId
            ));
            $requestObject->addData($params);
            $response = $this->soapModel
            ->setInputData($requestObject)
            ->updateCustomerProfile();
             $code = $response->reasonCode;
             if ($code == '100') {
                $newSubscriptionId = $response->paySubscriptionUpdateReply->subscriptionID;
                if (!empty($newSubscriptionId)) {
                    
                    $card->setFirstname($params['firstname']);
                    $card->setLastname($params['lastname']);
                    $card->setPostcode($params['postcode']);
                    $card->setCountryId($params['country_id']);
                    $card->setRegionId($params['region_id']);
                    $card->setState($params['state']);
                    $card->setCity($params['city']);
                    $card->setCompany($params['company']);
                    $card->setStreet($params['street']);
                    $card->setTelephone($params['telephone']);
                    $card->setCustomerId($customerid);
                    $card->setSubscriptionId($newSubscriptionId);
                    $card->setWebsiteId($params['website_id']);
                    if($params['cc_action']=='new'){
                        $card->setccType($params['cc_type']);
                        $card->setcc_exp_month($params['cc_exp_month']);
                        $card->setcc_exp_year($params['cc_exp_year']);
                        $card->setCcLast4(substr($params['cc_number'], -4, 4));
                    }
                    $card->setCreatedAt(date('Y-m-d H:i:s'));
                    $card->setUpdatedAt(date('Y-m-d H:i:s'));
                    $card->getResource()->save($card);
                    $returnArray['status'] = 1;
                    $returnArray['card'] = $card;
                    return $returnArray;
                }
            } else {
                $errorMessage = $this->cybersourceHelper->getErrorDescription($code);
                if ($code == '102' || $code == '101') {
                    $errorDescription = '';
                    if (isset($response->invalidField)) {
                        $errorDescription .= is_array($response->invalidField) ? implode(' ', $response->invalidField) : $response->invalidField;
                    }
                    if (isset($response->missingField)) {
                        $errorDescription .= is_array($response->missingField) ? implode(' ', $response->missingField) : $response->missingField;
                    }
                }
                if (isset($errorDescription) && !empty($errorDescription)) {
                    $message = __('Error code:') ." ".$code ." : " .$errorMessage. " : " . $errorDescription;
                    $returnArray['error'] = $message;
                    $returnArray['status'] = 0;
                    return $returnArray;
                } else {
                    $message = __('Error code:') ." ".$code ." : " .$errorMessage;
                    $returnArray['error'] = $message;
                    $returnArray['status'] = 0;
                    return $returnArray;
                }
            }
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $returnArray['error'] = $e->getMessage();
            $returnArray['status'] = 0;
            $errormsg[] = $returnArray;
            return $errormsg;
        } catch (\Exception $e) {
            //$returnArray['error'] = __('unable to process request');
            $returnArray['error'] = $e->getMessage();
            $returnArray['status'] = 2;
            $errormsg[] = $returnArray;
            return $errormsg;
        }
    }
     
    /**
     * delete customer card
     *
     * @api
     *
     * @param int $customerid
     * @param int $customercardid
     *
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function deleteCustomerCard($customerid,$customercardid)
    {
        try {
               $cardModel = $this->cybersourceTokenRepository->getById($customercardid); 
               if($cardModel->getId()){
                   if($cardModel->getCustomerId() != $customerid){
                       $returnArray['error'] = "Card Not Found";
                       $returnArray['status'] = 0;
                       return $returnArray;
                   }
                   if($this->cybersourceTokenRepository->delete($customercardid)){
                       $returnArray['success'] = "Card has been deleted successfully";
                       $returnArray['status'] = 1;
                       return $returnArray;
                   }
                   else{
                       $returnArray['error'] = "Something went Wrong";
                       $returnArray['status'] = 0;
                       return $returnArray;
                   }
               }
               else{
                   $returnArray['error'] = "Card Not Found";
                   $returnArray['status'] = 0;
                   return $returnArray;
               }
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $returnArray['error'] = $e->getMessage();
            $returnArray['status'] = 0;
            return $returnArray;
        } catch (\Exception $e) {
            $returnArray['error'] = $e->getMessage();
            $returnArray['status'] = 2;
            return $returnArray;
        }
    }
    /**
     * available cards
     *
     * @api
     *
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getAvailCard()
    {
        try {
            $returnArray['status'] = 1;
            $returnArray['availableCardTypes'] = $this->cybersourceHelper->getCcAvailableCardTypes();
            $response[] = $returnArray;
            return $response;
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $returnArray['error'] = $e->getMessage();
            $returnArray['status'] = 0;
            $errormsg[] = $returnArray;
            return $errormsg;
        } catch (\Exception $e) {
            $returnArray['error'] = $e->getMessage();
            $returnArray['status'] = 2;
            $errormsg[] = $returnArray;
            return $errormsg;
        }
    }
}