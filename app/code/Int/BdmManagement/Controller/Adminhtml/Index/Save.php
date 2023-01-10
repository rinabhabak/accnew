<?php

namespace Int\BdmManagement\Controller\Adminhtml\Index;

use Magento\Backend\App\Action;
use \Magento\Framework\Exception\AlreadyExistsException;

class Save extends \Magento\Backend\App\Action
{
    /**
     * @var PostDataProcessor
     */
    protected $dataProcessor;
    
    /**
     * @var \Magento\Customer\Model\Customer
     */
    private $customer;
    
    /**
     * @param Action\Context $context
     * @param PostDataProcessor $dataProcessor
     */
	
	protected $accountManagementInterface;
	
    public function __construct(
        Action\Context $context,
        \Magento\Customer\Model\Customer $customer,
		\Magento\Customer\Api\AccountManagementInterface $accountManagementInterface,
        PostDataProcessor $dataProcessor
    )
    {
        $this->dataProcessor = $dataProcessor;
        $this->customer = $customer;
		$this->_customerAccountManagement = $accountManagementInterface;
        parent::__construct($context);
    }

    /**
     * {@inheritdoc}
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Int_BdmManagement::save');
    }

    /**
     * Save action
     *
     * @return void
     */
    public function execute()
    {
        $data = $this->getRequest()->getPostValue();
        if ($data) {
            $data = $this->dataProcessor->filter($data);
            $_customer = $this->_objectManager->create('Magento\Customer\Model\Customer');
            $isNew = false;
            $id = $this->getRequest()->getParam('entity_id');
            if ($id) {
                $_customer->load($id);
            }else{
                $isNew = true;
                if($this->customerExists($data['email'],$data['website_id'])){
                    $this->messageManager->addError(__('User with the same email already exists in an associated website. Please check in customer list.'));
                    $this->_redirect('*/*/');
                    return;
                }
            }
            
            $_customer->addData($data);

            if (!$this->dataProcessor->validate($data)) {
                $this->_redirect('*/*/edit', ['entity_id' => $_customer->getId(), '_current' => true]);
                return;
            }

            try {
                
                if($_customer->save()){
                    if($isNew==true){
                        //$_customer->sendNewAccountEmail();
						$this->_customerAccountManagement->initiatePasswordReset(
							$data['email'],
							'email_reset_pwa',
                            $data['website_id']
						);
                    }
                }
                $this->messageManager->addSuccess(__('The BDM has been saved.'));
                $this->_objectManager->get('Magento\Backend\Model\Session')->setFormData(false);
                if ($this->getRequest()->getParam('back')) {
                    $this->_redirect('*/*/edit', ['entity_id' => $_customer->getId(), '_current' => true]);
                    return;
                }
                $this->_redirect('*/*/');
                return;
            }catch (\Magento\Framework\Exception\AlreadyExistsException $e) {
                $this->_getSession()->addError($e->getMessage());
            }catch (\Magento\Framework\Model\Exception $e) {
                $this->messageManager->addError($e->getMessage());
            } catch (\RuntimeException $e) {
                $this->messageManager->addError($e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addException($e, __('Something went wrong while saving the data.'));
            }

            $this->_getSession()->setFormData($data);
            $this->_redirect('*/*/edit', ['entity_id' => $this->getRequest()->getParam('entity_id')]);
            return;
        }
        $this->_redirect('*/*/');
    }
    
    
    /**
     * @param string     $email
     * @param null $websiteId
     *
     * @return bool|\Magento\Customer\Model\Customer
     */
    protected function customerExists($email, $websiteId = 1)
    {
        $customer = $this->customer;
        if ($websiteId) {
            $customer->setWebsiteId($websiteId);
        }
        $customer->loadByEmail($email);
        if ($customer->getId()) {
            return $customer;
        }

        return false;
    }
}
