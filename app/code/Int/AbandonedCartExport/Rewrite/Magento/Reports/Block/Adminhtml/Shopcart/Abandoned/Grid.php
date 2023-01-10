<?php
/**
 * Copyright © Indusnet Technologies All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Int\AbandonedCartExport\Rewrite\Magento\Reports\Block\Adminhtml\Shopcart\Abandoned;

class Grid extends \Magento\Reports\Block\Adminhtml\Shopcart\Abandoned\Grid
{
    /**
     * Prepare columns
     *
     * @return \Magento\Backend\Block\Widget\Grid\Extended
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function _prepareColumns()
    {

        $this->addExportType('abandoned_cart/report/export7days', __('Last 7 days CSV'));

        $this->addExportType('abandoned_cart/report/export15days', __('Last 15 days CSV'));

        $this->addExportType('abandoned_cart/report/export30days', __('Last 30 days CSV'));
        $this->addExportType('abandoned_cart/report/export', __('Last 180 days CSV'));

        return parent::_prepareColumns();
    }
}

