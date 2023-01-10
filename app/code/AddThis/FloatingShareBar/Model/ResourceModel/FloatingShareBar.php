<?php

namespace AddThis\FloatingShareBar\Model\ResourceModel;

class FloatingShareBar extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * Model Initialization
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('addthis_floatingsharebar', 'id');
    }
}
