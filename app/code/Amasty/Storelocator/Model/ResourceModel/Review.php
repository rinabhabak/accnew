<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Storelocator
 */


namespace Amasty\Storelocator\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class Review extends AbstractDb
{
    const TABLE_NAME = 'amasty_amlocator_review';

    public function _construct()
    {
        $this->_init(self::TABLE_NAME, 'id');
    }
}
