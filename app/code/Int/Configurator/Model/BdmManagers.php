<?php
/**
 * Copyright © Indus Net Technologies All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Int\Configurator\Model;

use Magento\Framework\Model\AbstractModel;

class BdmManagers extends AbstractModel
{
    /**
     * Define resource model
     */
    protected function _construct()
    {
        $this->_init(\Int\Configurator\Model\ResourceModel\BdmManagers::class);
    }
}