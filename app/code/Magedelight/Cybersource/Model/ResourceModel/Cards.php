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
namespace Magedelight\Cybersource\Model\ResourceModel;

class Cards extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    public function _construct()
    {
        $this->_init('magedelight_cybersource', 'card_id');
    }

    public function checkIdentifier($identifier, $storeId)
    {
        $select = $this->_getLoadByIdentifierSelect($identifier, 1);

        return $this->getConnection()->fetchOne($select);
    }
    protected function _getLoadByIdentifierSelect($identifier, $isActive = null)
    {
        $select = $this->getConnection()->select()->from(
            ['magedelightbdrand' => $this->getMainTable()]
        )->where('magedelightbdrand.status = ?', $isActive)
        ->where(
            'magedelightbdrand.url = ?',
            $identifier
        )
        ;

        return $select;
    }
}
