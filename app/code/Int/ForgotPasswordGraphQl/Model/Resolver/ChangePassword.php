<?php

 /**
 * Indusnet Technologies.
 *
 * @category  Indusnet
 * @package   Int_ForgotPasswordGraphQl
 * @author    Indusnet
 */
namespace Int\ForgotPasswordGraphQl\Model\Resolver;

use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Exception\GraphQlNoSuchEntityException;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Customer\Api\AccountManagementInterface;
use Magento\Customer\Model\ForgotPasswordToken\ConfirmCustomerByToken;
use Magento\Framework\App\ObjectManager;

class ChangePassword implements ResolverInterface
{
        /**
        * @var GetCustomerByToken
        */
        private $confirmByToken;
        /**
     * @var \Magento\Customer\Api\AccountManagementInterface
     */
        protected $_customerFactory;
        protected $customerRepository;

    protected $accountManagement;

    public function __construct(
        AccountManagementInterface $accountManagement,
        ConfirmCustomerByToken $confirmByToken = null,
        \Magento\Customer\Model\CustomerFactory $customerFactory,
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository
    ){
        $this->accountManagement = $accountManagement;
         $this->confirmByToken = $confirmByToken
            ?? ObjectManager::getInstance()->get(ConfirmCustomerByToken::class);
            $this->_customerFactory = $customerFactory;
            $this->customerRepository = $customerRepository;
    }

    /**
     * @inheritdoc
     */
    public function resolve(
        Field $field,
        $context,
        ResolveInfo $info,
        array $value = null,
        array $args = null
    ){
        try {
            if (!isset($args['newPasswordToken'])) {
              throw new GraphQlInputException(__('Password should be specified'));
            return false;
        }
        if ($args['newPassword'] !== $args['confirmPassword']) {
             throw new GraphQlInputException(__("New Password and Confirm New Password values didn't match."));
             return false;
        }
            $customerLoad = $this->_customerFactory->create()->getCollection()
                ->addAttributeToSelect("entity_id")
                ->addAttributeToFilter("rp_token", array("eq" => $args['newPasswordToken']))
                ->load();

                if($customerLoad->count() > 0){
                    $this->accountManagement->resetPassword(
                        null,
                        $args['newPasswordToken'],
                        $args['newPassword']
                    );
                    return [
                        "message" => __('You updated your password')
                    ];   
                }
                else{
                     return [
                        "message" => __('Your password reset link has expired. Please change your password from Forgot Password')
                    ];   
                }
            
          
        }catch (\Exception $exception) {
            throw new GraphQlNoSuchEntityException(__($exception->getMessage()));
        }
    }

    /**
     * @param array $args
     * @return string
     * @throws GraphQlInputException
     */
    private function validateEmail(array $args): string
    {
        if (!isset($args['email'])) {
            throw new GraphQlInputException(__('Email should be specified'));
            return false;
        }

        if (!\Zend_Validate::is($args['email'], \Magento\Framework\Validator\EmailAddress::class)) {
            throw new GraphQlInputException(__('The email address is incorrect. Verify the email address and try again.'));
            return false;
        }

        return trim($args['email']);
    }
}
