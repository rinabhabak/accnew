<?php

namespace Int\BdmManagement\Controller\Adminhtml\Index;

use Magento\Framework\Controller\ResultFactory;

/**
 * MassDelete action.
 */
class MassDelete extends \Magento\Backend\App\Action
{

    public function execute()
    {
        //print_r($this->getRequest()->getParams());exit;
        $entityIds = $this->getRequest()->getParam('entity_ids');
        //$entityIds = explode(',',$entityIds);
        if (!is_array($entityIds) || empty($entityIds)) {
            $this->messageManager->addErrorMessage(__('Please select item(s).'));
        } else {
            try {
                foreach ($entityIds as $entityId) {
                    $customer = $this->_objectManager->create('Magento\Customer\Model\Customer')
                        ->load($entityId);
                    $customer->delete();
                }
                $this->messageManager->addSuccessMessage(
                    __('A total of %1 record(s) have been deleted.', count($entityIds))
                );
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            }
        }
        $resultRedirect = $this->resultRedirectFactory->create();
        return $resultRedirect->setPath('*/*/');
    }
}