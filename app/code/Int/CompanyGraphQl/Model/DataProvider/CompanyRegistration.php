<?php
namespace Int\CompanyGraphQl\Model\DataProvider;

 use Magento\Framework\App\Action\HttpPostActionInterface;
 use Magento\Contact\Model\ConfigInterface;
 use Magento\Contact\Model\MailInterface;
 use Magento\Framework\App\Action\Context;
 use Magento\Framework\Controller\Result\Redirect;
 use Magento\Framework\Exception\LocalizedException;
 use Magento\Framework\App\ObjectManager;
 use Magento\Framework\DataObject;
 use Magento\Framework\GraphQl\Exception\GraphQlNoSuchEntityException;


class CompanyRegistration
{
    /**
     * @var \Magento\Framework\App\Request\DataPersistorInterface
     */    
    private $_dataPersistor;

    /**
     * @var \Magento\Framework\Data\Form\FormKey
     */
    private $_formKey;

    /**
     * @var \Magento\Framework\Api\DataObjectHelper
     */
    private $objectHelper;

    /**
     * @var \Magento\Customer\Api\AccountManagementInterface
     */
    private $customerAccountManagement;

    /**
     * @var \Magento\Company\Model\Create\Session
     */
    private $companyCreateSession;

    /**
     * @var \Magento\Customer\Api\Data\CustomerInterfaceFactory
     */
    private $customerDataFactory;

    /**
     * @var \Magento\Company\Api\Data\CompanyInterfaceFactory
     */
    private $companyDataFactory;

    /**
     * @var \Magento\Company\Api\CompanyRepositoryInterface
     */
    private $companyRepository;

    /**
     * @var \Magento\Company\Api\Data\CompanyInterface
     */
    private $companyInterface;

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    private $objectmanager;

    /**
     * @var \Magento\Company\Model\Email\Sender
     */
    private $senderModel;

    /**
     * @var \Magento\Backend\Model\UrlInterface
     */
    private $urlBuilder;

    public function __construct(
        \Magento\Framework\App\Request\DataPersistorInterface $dataPersistor,
        \Magento\Customer\Api\Data\CustomerInterfaceFactory $customerDataFactory,
        \Magento\Customer\Api\AccountManagementInterface $customerAccountManagement,
        \Magento\Framework\Api\DataObjectHelper $objectHelper,
        \Magento\Company\Model\Create\Session $companyCreateSession,
        \Magento\Company\Api\Data\CompanyInterfaceFactory $companyDataFactory,
        \Magento\Company\Api\Data\CompanyInterface $companyInterface,
        \Magento\Company\Api\CompanyRepositoryInterface $companyRepository,
        \Magento\Framework\ObjectManagerInterface $objectmanager,
        \Magento\Framework\Data\Form\FormKey $formKey,
        \Magento\Company\Model\Email\Sender $senderModel,
        \Magento\Backend\Model\UrlInterface $urlBuilder
    ) {
        $this->_dataPersistor = $dataPersistor;
        $this->_formKey = $formKey;
        $this->customerDataFactory = $customerDataFactory;
        $this->objectHelper = $objectHelper;
        $this->customerAccountManagement = $customerAccountManagement;
        $this->companyCreateSession = $companyCreateSession;
        $this->companyDataFactory = $companyDataFactory;
        $this->companyRepository = $companyRepository;
        $this->companyInterface = $companyInterface;
        $this->objectmanager = $objectmanager;
        $this->senderModel = $senderModel;
        $this->urlBuilder = $urlBuilder;
    }

    public function register($data){

        $message = [];
        try {

            $customer = $this->customerDataFactory->create();

            $this->objectHelper->populateWithArray(
                $customer,
                $data,
                \Magento\Customer\Api\Data\CustomerInterface::class
            );
            try {
            $customer = $this->customerAccountManagement->createAccount($customer);
            } catch (\Exception $e) { 
                throw new GraphQlNoSuchEntityException(__($e->getMessage()));
            }
            $data['company']['super_user_id'] = $customer->getId();
             $data['company']['sales_representative_id'] = 1;

            $company = $this->companyDataFactory->create();

            $this->objectHelper->populateWithArray(
                $company,
                $data['company'],
                \Magento\Company\Api\Data\CompanyInterface::class
            );
            
            $company = $this->companyRepository->save($company);

            if(isset($data['job_title']) && !empty($data['job_title'])){
                

                $job_title_model = $this->objectmanager->create('Magento\Company\Model\Customer');

                $job_title_model->setCustomerId($customer->getId());
                $job_title_model->setCompanyId($company->getId());
                $job_title_model->setJobTitle($data['job_title']);
                if(isset($data['company']['telephone'])){
                    $job_title_model->setTelephone($data['company']['telephone']);
                }
                $job_title_model->setStatus(1);
                $job_title_model->save();
            }

            $companyUrl = $this->urlBuilder->getUrl('company/index/edit', ['id' => $company->getId()]);
            $this->senderModel->sendAdminNotificationEmail($customer,$data['company']['company_name'],$companyUrl);
            $message['message'] = "Thank you! We're reviewing your request and will contact you soon";
            
        } catch (\Exception $e) { 
            throw new GraphQlNoSuchEntityException(__($e->getMessage()));
            $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/companyRegistration.log');
            $logger = new \Zend\Log\Logger();
            $logger->addWriter($writer);
            $logger->info($e);
        }
        
        return $message;
    }

    /*
    * get form key
    *
    * @return string
    */
    public function getFormKey()
    {
        return $this->_formKey->getFormKey();
    }
}
