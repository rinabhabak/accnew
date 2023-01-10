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
 * Gateway vault payment token search result interface.
 *
 * @api
 * @since 100.1.0
 */
interface CybersourceTokenSearchResultsInterface extends \Magento\Framework\Api\SearchResultsInterface
{
    /**
     * Gets collection items.
     *
     * @return \Magedelight\Cybersource\Api\Data\CardManageInterface[] Array of collection items.
     */
    public function getItems();

    /**
     * Sets collection items.
     *
     * @param \Magedelight\Cybersource\Api\Data\CardManageInterface[] $items
     * @return $this
     */
    public function setItems(array $items);
}
