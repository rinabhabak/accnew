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
namespace Magedelight\Cybersource\Api;

interface CardManagementInterface
{
    /**
     * get customer card listing
     *
     * @api
     *
     * @param int $customerid
     *
      * @return \Magedelight\Cybersource\Api\Data\CardManageInterface[] Cybersource tokens search result interface.
     */
    public function getCardListing($customerid);
    
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
    public function addCustomerCard($customerid,\Magedelight\Cybersource\Api\Data\CardManageInterface $cybersourcecard);
    
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
    public function updateCustomerCard($customerid,\Magedelight\Cybersource\Api\Data\CardManageInterface $cybersourcecard);
    
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
    public function deleteCustomerCard($customerid,$customercardid);
    
    /**
     * available cards
     *
     * @api
     *
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getAvailCard();
}