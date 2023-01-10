<?php
/**
 * Quote Form Links
 *
 * @category    Alpine
 * @package     Alpine_Cms
 * @copyright   Copyright (c) 2018 Alpine Consulting Inc.
 * @author      Dmitry Naumov <dmitry.naumov@alpineinc.com>
 */

namespace Alpine\Cms\Model\Quote\System\Config\Backend;

use Magento\Cms\Model\Block;
use Magento\Contact\Model\System\Config\Backend\Links as MagentoLinks;
use Magento\Framework\DataObject\IdentityInterface;
use Magento\Store\Model\Store;

/**
 * Cache cleaner backend model
 *
 * @category    Alpine
 * @package     Alpine_Cms
 */
class Links extends MagentoLinks implements IdentityInterface
{
    /**
     * Get identities
     *
     * @return array
     */
    public function getIdentities()
    {
        return [Store::CACHE_TAG, Block::CACHE_TAG];
    }
}