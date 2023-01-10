<?php
/**
 * Alpine_Catalog
 *
 * @category    Alpine
 * @package     Alpine_Catalog
 * @copyright   Copyright (c) 2018 Alpine Consulting, Inc
 * @author      Aleksandr Mikhailov (aleksandr.mikhailov@alpineinc.com)
 */

namespace Alpine\Catalog\Model\Product\ProductList;

use Magento\Catalog\Model\Product\ProductList\Toolbar;

/**
 * Class OtherProductsToolbar
 *
 * @category    Alpine
 * @package     Alpine_Catalog
 */
class OtherProductsToolbar extends Toolbar
{
    /**
     * GET parameter page variable name
     */
    const PAGE_PARM_NAME = 'o_p';

    /**
     * Sort order cookie name
     */
    const ORDER_PARAM_NAME = 'o_product_list_order';

    /**
     * Sort direction cookie name
     */
    const DIRECTION_PARAM_NAME = 'o_product_list_dir';

    /**
     * Sort mode cookie name
     */
    const MODE_PARAM_NAME = 'o_product_list_mode';

    /**
     * Get sort order
     *
     * @return string|bool
     */
    public function getOrder()
    {
        return $this->request->getParam(self::ORDER_PARAM_NAME);
    }

    /**
     * Get sort direction
     *
     * @return string|bool
     */
    public function getDirection()
    {
        return $this->request->getParam(self::DIRECTION_PARAM_NAME);
    }

    /**
     * Get sort mode
     *
     * @return string|bool
     */
    public function getMode()
    {
        return $this->request->getParam(self::MODE_PARAM_NAME);
    }

    /**
     * Return current page from request
     *
     * @return int
     */
    public function getCurrentPage()
    {
        $page = (int) $this->request->getParam(self::PAGE_PARM_NAME);
        return $page ? $page : 1;
    }
}