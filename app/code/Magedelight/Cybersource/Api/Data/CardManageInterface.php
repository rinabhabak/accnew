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
namespace Magedelight\Cybersource\Api\Data;

/**
 * Card Management interface.
 *
 * @api
 */
interface CardManageInterface
{
    /**#@+
     * Constants for keys of data array. Identical to the name of the getter in snake case
     */
    const ENTITY_ID = 'card_id';
    const WEBSITE_ID = 'website_id';
    const CUSTOMER_ID = 'customer_id';
    const SUBSCRIPTION_ID = 'subscription_id';
    const FIRSTNAME = 'firstname';
    const LASTNAME = 'lastname';
    const POSTCODE = 'postcode';
    const COUNTRY_ID = 'country_id';
    const REGION_ID = 'region_id';
    const STATE = 'state';
    const CITY = 'city';
    const COMPANY = 'company';
    const STREET = 'street';
    const TELEPHONE = 'telephone';
    const CCEXPMONTH = 'cc_exp_month';
    const CCLAST4 = 'cc_last_4';
    const CCTYPE = 'cc_type';
    const CCEXPYEAR = 'cc_exp_year';
    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';
    const CCNUMBER = 'cc_number';
    const CCACTION = 'cc_action';
    const CCCID = 'cc_cid';
    
    /**
     * Get ID.
     *
     * @return int|null
     */
    public function getId();

    /**
     * Set ID.
     *
     * @param int $id
     *
     * @return \Magedelight\Cybersource\Api\Data\CardManageInterface
     */
    public function setId($id);
    
    /**
     * Get WebsiteId.
     *
     * @return int|null
     */
    public function getWebsiteId();

    /**
     * Set WebsiteId.
     *
     * @param int $websiteId
     *
     * @return \Magedelight\Cybersource\Api\Data\CardManageInterface
     */
    public function setWebsiteId($websiteId);
    
    /**
     * Get CustomerId.
     *
     * @return int|null
     */
    public function getCustomerId();

    /**
     * Set CustomerId.
     *
     * @param int $customerid
     *
     * @return \Magedelight\Cybersource\Api\Data\CardManageInterface
     */
    public function setCustomerId($customerid);
    
    /**
     * Get SubscriptionId.
     *
     * @return string|null
     */
    public function getSubscriptionId();

    /**
     * Set SubscriptionId.
     *
     * @param int $subscriptionId
     *
     * @return \Magedelight\Cybersource\Api\Data\CardManageInterface
     */
    public function setSubscriptionId($subscriptionId);
    
    /**
     * Get Firstname.
     *
     * @return string|null
     */
    public function getFirstname();

    /**
     * Set Firstname.
     *
     * @param string|null
     *
     * @return \Magedelight\Cybersource\Api\Data\CardManageInterface
     */
    public function setFirstname($firstname);
    
    /**
     * Get Lastname.
     *
     * @return string|null
     */
    public function getLastname();

    /**
     * Set Lastname.
     *
     * @param string|null
     *
     * @return \Magedelight\Cybersource\Api\Data\CardManageInterface
     */
    public function setLastname($lastname);
    
    /**
     * Get Postcode.
     *
     * @return string|null
     */
    public function getPostcode();

    /**
     * Set Postcode.
     *
     * @param string|null
     *
     * @return \Magedelight\Cybersource\Api\Data\CardManageInterface
     */
    public function setPostcode($postcode);
    
    /**
     * Get CountryId.
     *
     * @return string|null
     */
    public function getCountryId();

    /**
     * Set CountryId.
     *
     * @param string|null
     *
     * @return \Magedelight\Cybersource\Api\Data\CardManageInterface
     */
    public function setCountryId($countryId);
    
    /**
     * Get RegionId.
     *
     * @return int|null
     */
    public function getRegionId();

    /**
     * Set RegionId.
     *
     * @param int $id
     *
     * @return \Magedelight\Cybersource\Api\Data\CardManageInterface
     */
    public function setRegionId($regionId);
  
    /**
     * Get State.
     *
     * @return string|null
     */
    public function getState();

    /**
     * Set State.
     *
     * @param string|null
     *
     * @return \Magedelight\Cybersource\Api\Data\CardManageInterface
     */
    public function setState($state);
    
    /**
     * Get City.
     *
     * @return string|null
     */
    public function getCity();

    /**
     * Set City.
     *
     * @param string|null
     *
     * @return \Magedelight\Cybersource\Api\Data\CardManageInterface
     */
    public function setCity($city);
    
    /**
     * Get Company.
     *
     * @return string|null
     */
    public function getCompany();

    /**
     * Set Company.
     *
     * @param string|null
     *
     * @return \Magedelight\Cybersource\Api\Data\CardManageInterface
     */
    public function setCompany($company);
    
    /**
     * Get Street.
     *
     * @return string|null
     */
    public function getStreet();

    /**
     * Set Street.
     *
     * @param string|null
     *
     * @return \Magedelight\Cybersource\Api\Data\CardManageInterface
     */
    public function setStreet($street);
    
    /**
     * Get Telephone.
     *
     * @return string|null
     */
    public function getTelephone();

    /**
     * Set Telephone.
     *
     * @param string|null
     *
     * @return \Magedelight\Cybersource\Api\Data\CardManageInterface
     */
    public function setTelephone($telephone);
    
    /**
     * Get CcExpMonth.
     *
     * @return string|null
     */
    public function getCcExpMonth();

    /**
     * Set CcExpMonth.
     *
     * @param string|null
     *
     * @return \Magedelight\Cybersource\Api\Data\CardManageInterface
     */
    public function setCcExpMonth($ccExpMonth);
    
    /**
     * Get CcLast4.
     *
     * @return string|null
     */
    public function getCcLast4();

    /**
     * Set CcLast4.
     *
     * @param string|null
     *
     * @return \Magedelight\Cybersource\Api\Data\CardManageInterface
     */
    public function setCcLast4($ccLast4);
    
    /**
     * Get CcType.
     *
     * @return string|null
     */
    public function getCcType();

    /**
     * Set CcType.
     *
     * @param string|null
     *
     * @return \Magedelight\Cybersource\Api\Data\CardManageInterface
     */
    public function setCcType($ccType);
    
    /**
     * Get CcExpYea.
     *
     * @return string|null
     */
    public function getCcExpYear();

    /**
     * Set CcExpYea.
     *
     * @param string|null
     *
     * @return \Magedelight\Cybersource\Api\Data\CardManageInterface
     */
    public function setCcExpYear($ccLast4);
    
    /**
     * Get CreatedAt.
     *
     * @return string|null
     */
    public function getCreatedAt();

    /**
     * Set CreatedAt.
     *
     * @param string|null
     *
     * @return \Magedelight\Cybersource\Api\Data\CardManageInterface
     */
    public function setCreatedAt($cratedAt);
    
    /**
     * Get UpdatedAt.
     *
     * @return string|null
     */
    public function getUpdatedAt();

    /**
     * Set UpdatedAt.
     *
     * @param string|null
     *
     * @return \Magedelight\Cybersource\Api\Data\CardManageInterface
     */
    public function setUpdatedAt($updatedAt);
    
    /**
     * Get CcNumber.
     *
     * @return string|null
     */
    public function getCcNumber();

    /**
     * Set CcNumber.
     *
     * @param string|null
     *
     * @return \Magedelight\Cybersource\Api\Data\CardManageInterface
     */
    public function setCcNumber($ccNumber);
    
    /**
     * Get CcAction.
     *
     * @return string|null
     */
    public function getCcAction();

    /**
     * Set CcAction.
     *
     * @param string|null
     *
     * @return \Magedelight\Cybersource\Api\Data\CardManageInterface
     */
    public function setCcAction($ccaction);
    
    /**
     * Get CcCid.
     *
     * @return string|null
     */
    public function getCcCid();

    /**
     * Set CcCid.
     *
     * @param string|null
     *
     * @return \Magedelight\Cybersource\Api\Data\CardManageInterface
     */
    public function setCcCid($cccid);
}