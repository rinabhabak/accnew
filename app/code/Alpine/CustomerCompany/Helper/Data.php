<?php
/**
 * Alpine_CustomerCompany
 *
 * @category    Alpine
 * @package     Alpine_CustomerCompany
 * @copyright   Copyright (c) 2018 Alpine Consulting, Inc
 * @author      Andrey Nesterov <andrey.nesterov@alpineinc.com>
 */
namespace Alpine\CustomerCompany\Helper;

use Magento\Framework\App\Helper\AbstractHelper;

/**
 * Alpine\CustomerCompany\Helper\Data
 *
 * @category    Alpine
 * @package     Alpine_CustomerCompany
 */
class Data extends AbstractHelper
{
    /**
     * Constants for attributes code
     *
     * @var string
     */
    const PICK_YOUR_INDUSTRY_CODE = 'pick_your_industry';
    const OTHER_INDUSRTY_CODE = 'other_industry';
    const NEWSLETTER_CODE = 'newsletter';
}
