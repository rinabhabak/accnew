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

/**
 * Cybersource token repository interface.
 *
 * @api
 */
interface CybersourceTokenRepositoryInterface
{
    /**
     * Lists cybersource tokens that match specified search criteria.
     *
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria The search criteria.
     * @return \Magedelight\Cybersource\Api\Data\CybersourceTokenSearchResultsInterface Cybersource token search result interface.
     */
    public function getList(\Magento\Framework\Api\SearchCriteriaInterface $searchCriteria);

    /**
     * Loads a specified payment token.
     *
     * @param int $entityId The cybersource token entity ID.
     * @return \Magedelight\Cybersource\Api\Data\CardManageInterface Cybersource token interface.
     */
    public function getById($entityId);

    /**
     * Deletes a specified payment token.
     *
     * @param int $entityId The cybersource token entity ID.
     * @return bool
     */
    public function delete($entityId);

    /**
     * Performs persist operations for a specified payment token.
     *
     * @param \Magedelight\Cybersource\Api\Data\CardManageInterface $cybersourcecard The payment token.
     * @return \Magedelight\Cybersource\Api\Data\CardManageInterface Cybersource token interface.
     * @since 100.1.0
     */
    public function save(Data\CardManageInterface $cybersourcecard);
}
