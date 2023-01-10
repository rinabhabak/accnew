<?php
  /**
 * Indusnet Technologies.
 *
 * @category  Indusnet
 * @package   Int_ForgotPassword
 * @author    Indusnet
 */
namespace Int\ForgotPassword\Model;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\Mail\Template\TransportBuilder;
use Magento\Customer\Helper\View as CustomerViewHelper;
use Magento\Framework\Reflection\DataObjectProcessor;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Mail\Template\SenderResolverInterface;
use Magento\Framework\App\ObjectManager;
 
class EmailNotification extends \Magento\Customer\Model\EmailNotification
 
{
	const XML_PATH_FORGOT_EMAIL_PWA_TEMPLATE = 'customer/password/reset_password_template_pwa';

    const XML_PATH_FORGOT_EMAIL_IDENTITY = 'customer/password/forgot_email_identity';

    const FORGET_PASSWORD_PWA_URL = 'customer/password/forget_password_pwa_url';

	const XML_PATH_FORGOT_EMAIL_TEMPLATE = 'customer/password/reset_password_template';
	
    private $customerRegistry;
     /**
     * @var StoreManagerInterface
     */
    private $storeManager;
    /**
     * @var TransportBuilder
     */
    private $transportBuilder;
    /**
     * @var CustomerViewHelper
     */
    protected $customerViewHelper;

     /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;
     /**
     * @var SenderResolverInterface
     */
    private $senderResolver;
    
    /**
     * @var GroupRepositoryInterface
     */
    protected $customerGroupInterface;

    public function __construct(
        \Magento\Customer\Model\CustomerRegistry $customerRegistry,
        StoreManagerInterface $storeManager,
        TransportBuilder $transportBuilder,
        CustomerViewHelper $customerViewHelper,
        DataObjectProcessor $dataProcessor,
        ScopeConfigInterface $scopeConfig,
        \Magento\Customer\Api\GroupRepositoryInterface $customerGroupInterface,
        SenderResolverInterface $senderResolver = null
    ){
        parent::__construct($customerRegistry,$storeManager,$transportBuilder,$customerViewHelper,$dataProcessor,$scopeConfig);
        $this->customerRegistry = $customerRegistry;
        $this->storeManager = $storeManager;
        $this->transportBuilder = $transportBuilder;
        $this->customerViewHelper = $customerViewHelper;
        $this->dataProcessor = $dataProcessor;
        $this->scopeConfig = $scopeConfig;
        $this->customerGroupInterface = $customerGroupInterface;
        $this->senderResolver = $senderResolver ?: ObjectManager::getInstance()->get(SenderResolverInterface::class);
    }

     /**
     * Send email with reset password confirmation link
     *
     * @param CustomerInterface $customer
     * @return void
     */
    public function passwordResetConfirmationPwa(CustomerInterface $customer,$newPasswordToken)
    {
        $storeId = $this->getCustomerWebsiteStoreId($customer);
        $customerEmailData = $this->getFullCustomerObject($customer);
        $forget_password_pwa_url = $this->scopeConfig->getValue( self::FORGET_PASSWORD_PWA_URL,
                                    \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                                    $storeId );
        
        $group = $this->customerGroupInterface->getById($customer->getGroupId());
                
        if($group->getCode()=='BDS' || $group->getCode()=='BDM Manager') {
			$this->sendEmailTemplate(
				$customer,
				self::XML_PATH_FORGOT_EMAIL_PWA_TEMPLATE,
				self::XML_PATH_FORGOT_EMAIL_IDENTITY,
				['customer' => $customerEmailData, 'store' => $this->storeManager->getStore($storeId),'pwa' => $forget_password_pwa_url. '?token='.$newPasswordToken],
				$storeId
			);
            
		}else{
            $this->sendEmailTemplate(
				$customer,
				self::XML_PATH_FORGOT_EMAIL_TEMPLATE,
				self::XML_PATH_FORGOT_EMAIL_IDENTITY,
				['customer' => $customerEmailData, 'store' => $this->storeManager->getStore($storeId),'pwa' => $forget_password_pwa_url. '?token='.$newPasswordToken],
				$storeId
			);
			
		}
                
    }

    /**
     * Create an object with data merged from Customer and CustomerSecure
     *
     * @param CustomerInterface $customer
     * @return \Magento\Customer\Model\Data\CustomerSecure
     */
    private function getFullCustomerObject($customer)
    {
        // No need to flatten the custom attributes or nested objects since the only usage is for email templates and
        // object passed for events


        $mergedCustomerData = $this->customerRegistry->retrieveSecureData($customer->getId());
        $customerData = $this->dataProcessor
            ->buildOutputDataArray($customer, \Magento\Customer\Api\Data\CustomerInterface::class);
        $mergedCustomerData->addData($customerData);
        $mergedCustomerData->setData('name', $this->customerViewHelper->getCustomerName($customer));
        return $mergedCustomerData;
    }

    /**
     * Send corresponding email template
     *
     * @param CustomerInterface $customer
     * @param string $template configuration path of email template
     * @param string $sender configuration path of email identity
     * @param array $templateParams
     * @param int|null $storeId
     * @param string $email
     * @return void
     * @throws \Magento\Framework\Exception\MailException
     */
    private function sendEmailTemplate(
        $customer,
        $template,
        $sender,
        $templateParams = [],
        $storeId = null,
        $email = null
    ) {
        $templateId = $this->scopeConfig->getValue($template, 'store', $storeId);
		
        if ($email === null) {
            $email = $customer->getEmail();
        }

        /** @var array $from */
        $from = $this->senderResolver->resolve(
            $this->scopeConfig->getValue($sender, 'store', $storeId),
            $storeId
        );

        $transport = $this->transportBuilder->setTemplateIdentifier($templateId)
            ->setTemplateOptions(['area' => 'frontend', 'store' => $storeId])
            ->setTemplateVars($templateParams)
            ->setFrom($from)
            ->addTo($email, $this->customerViewHelper->getCustomerName($customer))
            ->getTransport();

        $transport->sendMessage();
    }
    
    
    
    /**
     * Get either first store ID from a set website or the provided as default
     *
     * @param CustomerInterface $customer
     * @param int|string|null $defaultStoreId
     * @return int
     */
    private function getCustomerWebsiteStoreId($customer, $defaultStoreId = null)
    {
        if ($customer->getWebsiteId() != 0 && empty($defaultStoreId)) {
            $storeIds = $this->storeManager->getWebsite($customer->getWebsiteId())->getStoreIds();
            $defaultStoreId = reset($storeIds);
        }
        return $defaultStoreId;
    }
    
    
}