<?php
/**
 * Alpine_Catalog
 *
 * @category    Alpine
 * @package     Alpine_Catalog
 * @copyright   Copyright (c) 2018 Alpine Consulting, Inc
 * @author      Aleksandr Mikhailov <aleksandr.mikhailov@alpineinc.com>
 */

namespace Alpine\Catalog\Block\Html;

use Magento\Theme\Block\Html\Pager;

/**
 * Class OtherProductsPager
 *
 * @category    Alpine
 * @package     Alpine_Catalog
 */
class OtherProductsPager extends Pager
{
    /**
     * @var string
     */
    protected $_pageVarName = 'o_p';
}