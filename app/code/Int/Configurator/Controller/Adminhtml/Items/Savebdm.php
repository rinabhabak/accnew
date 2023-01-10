<?php
/**
 * Indusnet Technologies.
 *
 * @category  Indusnet
 * @package   Int_ConfiguratorGraphQl
 * @author    Indusnet
 */

namespace Int\Configurator\Controller\Adminhtml\Items;

class Savebdm extends \Int\Configurator\Controller\Adminhtml\Items
{
	/**
     * @var \Magento\Framework\Stdlib\DateTime\TimezoneInterface
     */
	protected $timezone;
	
	public function __construct(
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $timezone
    ) {
        $this->timezone = $timezone;
	}
	
    public function execute()
    {		
        if ($this->getRequest()->getPostValue()) {		
			
            try {               
				
                $postData = $this->getRequest()->getPostValue();
				$model = $this->_objectManager->create('Int\Configurator\Model\BdmManagers')->getCollection()->addFieldToFilter('parent_id',$postData['configuratorid'])->getData();
				if(count($model) > 0) {
						$id = $model[0]['entity_id'];
						$model = $this->_objectManager->create('Int\Configurator\Model\BdmManagers')->load($id);
						$model->setParentId($postData['configuratorid']);
						$model->setAssignedTo($postData['customerid']);
						$model->setAssignedBy('admin');
						$model->save();
						
						/*** change status of configurator ***/
						$modelConfigurator = $this->_objectManager->create('Int\Configurator\Model\Configurator')->load($postData['configuratorid']);
						$modelConfigurator->setStatus(2);
						$modelConfigurator->save();
				}
				else{
						$model = $this->_objectManager->create('Int\Configurator\Model\BdmManagers');
						$model->setParentId($postData['configuratorid']);
						$model->setAssignedTo($postData['customerid']);
						$model->setAssignedBy('admin');
						$model->save();
						
						/*** change status of configurator ***/
						$modelConfigurator = $this->_objectManager->create('Int\Configurator\Model\Configurator')->load($postData['configuratorid']);
						$modelConfigurator->setStatus(2);
						$modelConfigurator->save();
				}				
                /******** update history records ********/
			    $customerHistoryUpdates = $this->_objectManager->create('Int\CustomerHistoryUpdates\Model\Status')->getCollection()->addFieldToFilter('configurator_id',$postData['configuratorid'])->getData();
				if(count($customerHistoryUpdates) > 0) {
					$id = $customerHistoryUpdates[0]['id'];
					$customerHistoryStatus = $this->_objectManager->create('Int\CustomerHistoryUpdates\Model\Status')->load($id);
					$customerHistoryStatus->setMessage('Your project has been processed');
					$customerHistoryStatus->setUpdatedAt($this->timezone->date()->format('Y-m-d H:i:s'));
					$customerHistoryStatus->save();
				}
				
                $this->messageManager->addSuccess(__('BDM has been assigned successfully.'));
                
                $this->_redirect('int_configurator/*/');
                return;
				
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $this->messageManager->addError($e->getMessage());
                $this->_redirect('int_configurator/*/');
                return;
            } catch (\Exception $e) {
                $this->messageManager->addError(
                    __('Something went wrong while saving the item data. Please review the error log.')
                );
                $this->_redirect('int_configurator/*/');
                return;
            }
        }
        $this->_redirect('int_configurator/*/');
    }
}
