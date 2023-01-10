<?php
/**
 * Copyright © 2015 AddThis. All rights reserved.
 */

namespace AddThis\FloatingShareBar\Model\ResourceModel\FloatingShareBar;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('AddThis\FloatingShareBar\Model\FloatingShareBar', 'AddThis\FloatingShareBar\Model\ResourceModel\FloatingShareBar');
    }
}
