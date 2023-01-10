<?php
/**
 * Adminhtml bdmmanagement list block
 *
 */
namespace Int\BdmManagement\Block\Adminhtml;

class BdmManagement extends \Magento\Backend\Block\Widget\Grid\Container
{
    /**
     * Constructor
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_controller = 'adminhtml_bdmManagement';
        $this->_blockGroup = 'Int_BdmManagement';
        $this->_headerText = __('BDM Manager');
        $this->_addButtonLabel = __('Add BDM');
        parent::_construct();
        if ($this->_isAllowedAction('Int_BdmManagement::save')) {
            $this->buttonList->update('add', 'label', __('Add BDS / BDM Manager'));
        } else {
            $this->buttonList->remove('add');
        }
    }
    
    /**
     * Check permission for passed action
     *
     * @param string $resourceId
     * @return bool
     */
    protected function _isAllowedAction($resourceId)
    {
        return $this->_authorization->isAllowed($resourceId);
    }
}
