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
namespace Magedelight\Cybersource\Model;

use Magedelight\Cybersource\Api\Data\CardManageInterface;

class Cards extends \Magento\Framework\Model\AbstractModel implements \Magedelight\Cybersource\Api\Data\CardManageInterface
{
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Encryption\EncryptorInterface $encryptor,    
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->encryptor = $encryptor;
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
    }

    public function _construct()
    {
        $this->_init('Magedelight\Cybersource\Model\ResourceModel\Cards');
    }

    public function checkIdentifier($identifier, $storeId)
    {
        return $this->_getResource()->checkIdentifier($identifier, $storeId);
    }
    
    /**
     * Get ID.
     *
     * @return int|null
     */
    public function getId() 
    {
        return $this->getData(CardManageInterface::ENTITY_ID);
    }

    /**
     * Set ID.
     *
     * @param int $id
     *
     * @return \Magedelight\Cybersource\Api\Data\CardManageInterface
     */
    public function setId($id)
    {
       $this->setData(CardManageInterface::ENTITY_ID, $id);
       return $this; 
    }
    
    /**
     * Get WebsiteId.
     *
     * @return int|null
     */
    public function getWebsiteId()
    {
        return $this->getData(CardManageInterface::WEBSITE_ID);
    }        

    /**
     * Set WebsiteId.
     *
     * @param int $websiteId
     *
     * @return \Magedelight\Cybersource\Api\Data\CardManageInterface
     */
    public function setWebsiteId($websiteId)
    {
       $this->setData(CardManageInterface::WEBSITE_ID, $websiteId);
       return $this; 
    }        
    
    /**
     * Get CustomerId.
     *
     * @return int|null
     */
    public function getCustomerId()
    {
        return $this->getData(CardManageInterface::CUSTOMER_ID);
    }

    /**
     * Set CustomerId.
     *
     * @param int $customerid
     *
     * @return \Magedelight\Cybersource\Api\Data\CardManageInterface
     */
    public function setCustomerId($customerid)
    {
       $this->setData(CardManageInterface::CUSTOMER_ID, $customerid);
       return $this; 
    }        
    
    /**
     * Get SubscriptionId.
     *
     * @return string|null
     */
    public function getSubscriptionId()
    {
        return $this->encryptor->encrypt($this->getData(CardManageInterface::SUBSCRIPTION_ID));
    }        

    /**
     * Set SubscriptionId.
     *
     * @param int $subscriptionId
     *
     * @return \Magedelight\Cybersource\Api\Data\CardManageInterface
     */
    public function setSubscriptionId($subscriptionId)
    {
       $this->setData(CardManageInterface::SUBSCRIPTION_ID, $subscriptionId);
       return $this; 
    }        
    
    /**
     * Get Firstname.
     *
     * @return string|null
     */
    public function getFirstname()
    {
        return $this->getData(CardManageInterface::FIRSTNAME);
    }        

    /**
     * Set Firstname.
     *
     * @param string|null
     *
     * @return \Magedelight\Cybersource\Api\Data\CardManageInterface
     */
    public function setFirstname($firstname)
    {
       $this->setData(CardManageInterface::FIRSTNAME, $firstname);
       return $this; 
    }        
    
    /**
     * Get Lastname.
     *
     * @return string|null
     */
    public function getLastname()
    {
        return $this->getData(CardManageInterface::LASTNAME);
    }        

    /**
     * Set Lastname.
     *
     * @param string|null
     *
     * @return \Magedelight\Cybersource\Api\Data\CardManageInterface
     */
    public function setLastname($lastname)
    {
       $this->setData(CardManageInterface::LASTNAME, $lastname);
       return $this; 
    }        
    
    /**
     * Get Postcode.
     *
     * @return string|null
     */
    public function getPostcode()
    {
        return $this->getData(CardManageInterface::POSTCODE);
    }        

    /**
     * Set Postcode.
     *
     * @param string|null
     *
     * @return \Magedelight\Cybersource\Api\Data\CardManageInterface
     */
    public function setPostcode($postcode)
    {
       $this->setData(CardManageInterface::POSTCODE, $postcode);
       return $this; 
    }        
    
    /**
     * Get CountryId.
     *
     * @return string|null
     */
    public function getCountryId()
    {
        return $this->getData(CardManageInterface::COUNTRY_ID);
    }        

    /**
     * Set CountryId.
     *
     * @param string|null
     *
     * @return \Magedelight\Cybersource\Api\Data\CardManageInterface
     */
    public function setCountryId($countryId)
    {
       $this->setData(CardManageInterface::COUNTRY_ID, $countryId);
       return $this; 
    }        
    
    /**
     * Get RegionId.
     *
     * @return int|null
     */
    public function getRegionId()
    {
        return $this->getData(CardManageInterface::REGION_ID);
    }        

    /**
     * Set RegionId.
     *
     * @param int $regionId
     *
     * @return \Magedelight\Cybersource\Api\Data\CardManageInterface
     */
    public function setRegionId($regionId)
    {
       $this->setData(CardManageInterface::REGION_ID, $regionId);
       return $this; 
    }        
  
    /**
     * Get State.
     *
     * @return string|null
     */
    public function getState()
    {
        return $this->getData(CardManageInterface::STATE);
    }        

    /**
     * Set State.
     *
     * @param string|null
     *
     * @return \Magedelight\Cybersource\Api\Data\CardManageInterface
     */
    public function setState($state)
    {
       $this->setData(CardManageInterface::STATE, $state);
       return $this; 
    }             
    
    /**
     * Get City.
     *
     * @return string|null
     */
    public function getCity()
    {
        return $this->getData(CardManageInterface::CITY);
    }        

    /**
     * Set City.
     *
     * @param string|null
     *
     * @return \Magedelight\Cybersource\Api\Data\CardManageInterface
     */
    public function setCity($city)
    {
       $this->setData(CardManageInterface::CITY, $city);
       return $this; 
    }             
    
    /**
     * Get Company.
     *
     * @return string|null
     */
    public function getCompany()
    {
        return $this->getData(CardManageInterface::COMPANY);
    }        

    /**
     * Set Company.
     *
     * @param string|null
     *
     * @return \Magedelight\Cybersource\Api\Data\CardManageInterface
     */
    public function setCompany($company)
    {
       $this->setData(CardManageInterface::COMPANY, $company);
       return $this; 
    }             
    
    /**
     * Get Street.
     *
     * @return string|null
     */
    public function getStreet()
    {
        return $this->getData(CardManageInterface::STREET);
    }        

    /**
     * Set Street.
     *
     * @param string|null
     *
     * @return \Magedelight\Cybersource\Api\Data\CardManageInterface
     */
    public function setStreet($street)
    {
       $this->setData(CardManageInterface::STREET, $street);
       return $this; 
    }             
    
    /**
     * Get Telephone.
     *
     * @return string|null
     */
    public function getTelephone()
    {
        return $this->getData(CardManageInterface::TELEPHONE);
    }        

    /**
     * Set Telephone.
     *
     * @param string|null
     *
     * @return \Magedelight\Cybersource\Api\Data\CardManageInterface
     */
    public function setTelephone($telephone)
    {
       $this->setData(CardManageInterface::TELEPHONE, $telephone);
       return $this; 
    }             
    
    /**
     * Get CcExpMonth.
     *
     * @return string|null
     */
    public function getCcExpMonth()
    {
        return $this->getData(CardManageInterface::CCEXPMONTH);
    }        

    /**
     * Set CcExpMonth.
     *
     * @param string|null
     *
     * @return \Magedelight\Cybersource\Api\Data\CardManageInterface
     */
    public function setCcExpMonth($ccExpMonth)
    {
       $this->setData(CardManageInterface::CCEXPMONTH, $ccExpMonth);
       return $this; 
    }             
    
    /**
     * Get CcLast4.
     *
     * @return string|null
     */
    public function getCcLast4()
    {
        return $this->getData(CardManageInterface::CCLAST4);
    }        

    /**
     * Set CcLast4.
     *
     * @param string|null
     *
     * @return \Magedelight\Cybersource\Api\Data\CardManageInterface
     */
    public function setCcLast4($ccLast4)
    {
       $this->setData(CardManageInterface::CCLAST4, $ccLast4);
       return $this; 
    }             
    
    /**
     * Get CcType.
     *
     * @return string|null
     */
    public function getCcType()
    {
        return $this->getData(CardManageInterface::CCTYPE);
    }

    /**
     * Set CcType.
     *
     * @param string|null
     *
     * @return \Magedelight\Cybersource\Api\Data\CardManageInterface
     */
    public function setCcType($ccType)
    {
       $this->setData(CardManageInterface::CCTYPE, $ccType);
       return $this; 
    }             
    
    /**
     * Get CcExpYea.
     *
     * @return string|null
     */
    public function getCcExpYear()
    {
        return $this->getData(CardManageInterface::CCEXPYEAR);
    }         

    /**
     * Set CcExpYea.
     *
     * @param string|null
     *
     * @return \Magedelight\Cybersource\Api\Data\CardManageInterface
     */
    public function setCcExpYear($ccLast4)
    {
       $this->setData(CardManageInterface::CCEXPYEAR, $ccLast4);
       return $this; 
    }             
    
    /**
     * Get CreatedAt.
     *
     * @return string|null
     */
    public function getCreatedAt()
    {
        return $this->getData(CardManageInterface::CREATED_AT);
    }        

    /**
     * Set CreatedAt.
     *
     * @param string|null
     *
     * @return \Magedelight\Cybersource\Api\Data\CardManageInterface
     */
    public function setCreatedAt($cratedAt)
    {
       $this->setData(CardManageInterface::CREATED_AT, $cratedAt);
       return $this; 
    }             
    
    /**
     * Get UpdatedAt.
     *
     * @return string|null
     */
    public function getUpdatedAt()
    {
        return $this->getData(CardManageInterface::UPDATED_AT);
    }        

    /**
     * Set UpdatedAt.
     *
     * @param string|null
     *
     * @return \Magedelight\Cybersource\Api\Data\CardManageInterface
     */
    public function setUpdatedAt($updatedAt)
    {
       $this->setData(CardManageInterface::UPDATED_AT, $updatedAt);
       return $this; 
    }             
    
    /**
     * Get CcNumber.
     *
     * @return string|null
     */
    public function getCcNumber()
    {
        return $this->getData(CardManageInterface::CCNUMBER);
    }        

    /**
     * Set CcNumber.
     *
     * @param string|null
     *
     * @return \Magedelight\Cybersource\Api\Data\CardManageInterface
     */
    public function setCcNumber($ccNumber)
    {
       $this->setData(CardManageInterface::CCNUMBER, $ccNumber);
       return $this; 
    }   
    
    /**
     * Get CcAction.
     *
     * @return string|null
     */
    public function getCcAction()
    {
        return $this->getData(CardManageInterface::CCACTION);
    }

    /**
     * Set CcAction.
     *
     * @param string|null
     *
     * @return \Magedelight\Cybersource\Api\Data\CardManageInterface
     */
    public function setCcAction($ccaction)
    {
       $this->setData(CardManageInterface::CCACTION, $ccaction);
       return $this; 
    }
    
    /**
     * Get CcCid.
     *
     * @return string|null
     */
    public function getCcCid()
    {
        return $this->getData(CardManageInterface::CCCID);
    }

    /**
     * Set CcCid.
     *
     * @param string|null
     *
     * @return \Magedelight\Cybersource\Api\Data\CardManageInterface
     */
    public function setCcCid($cccid)
    {
       $this->setData(CardManageInterface::CCCID, $cccid);
       return $this; 
    }
}
