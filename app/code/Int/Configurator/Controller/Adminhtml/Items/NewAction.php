<?php
/**
 * Indusnet Technologies.
 *
 * @category  Indusnet
 * @package   Int_ConfiguratorGraphQl
 * @author    Indusnet
 */

namespace Int\Configurator\Controller\Adminhtml\Items;

class NewAction extends \Int\Configurator\Controller\Adminhtml\Items
{

    public function execute()
    {
        $this->_forward('edit');
    }
}
