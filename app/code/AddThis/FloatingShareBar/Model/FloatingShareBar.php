<?php
/**
 * Copyright © 2015 AddThis. All rights reserved.
 */

namespace AddThis\FloatingShareBar\Model;

class FloatingShareBar extends \Magento\Framework\Model\AbstractModel
{
    /**
     * Constructor
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->_init('AddThis\FloatingShareBar\Model\ResourceModel\FloatingShareBar');
    }
}
