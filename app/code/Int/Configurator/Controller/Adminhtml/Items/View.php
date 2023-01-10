<?php
/**
 * Indusnet Technologies.
 *
 * @category  Indusnet
 * @package   Int_ConfiguratorGraphQl
 * @author    Indusnet
 */

namespace Int\Configurator\Controller\Adminhtml\Items;

class View extends \Magento\Backend\App\Action
{
    public function execute()
    {   
     
	    $this->_view->loadLayout();
		$title = __('Configurator Details');
		$this->_view->getPage()->getConfig()->getTitle()->prepend($title);
        $this->_view->getLayout()->initMessages();
		
        $this->_view->renderLayout();
    }
}