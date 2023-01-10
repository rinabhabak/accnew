<?php
/**
 * Indusnet Technologies.
 *
 * @category  Indusnet
 * @package   Int_ConfiguratorGraphQl
 * @author    Indusnet
 */

namespace Int\Configurator\Model;

use Magento\Framework\Model\AbstractModel;

class Configurator extends AbstractModel
{
    /**
     * Define resource model
     */
    protected function _construct()
    {
        $this->_init('Int\Configurator\Model\ResourceModel\Configurator');
    }
}