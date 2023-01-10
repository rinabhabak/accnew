<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_ProductAttachment
 */


namespace Int\ProductDownload\Model;

class ConfigProvider extends \Amasty\ProductAttachment\Model\ConfigProvider
{
    protected $pathPrefix = 'amfile/';
    
    /**#@+
     * Constants defined for xpath of system configuration
     */
    const BLOCK_CUSTOMER_GROUPS_UPDATED = 'product_tab/customer_group_updated';
    /**#@-*/

    /**
     * @return string
     */
    public function getBlockCustomerGroups()
    {
        return $this->getValue(self::BLOCK_CUSTOMER_GROUPS_UPDATED);
    }
}
