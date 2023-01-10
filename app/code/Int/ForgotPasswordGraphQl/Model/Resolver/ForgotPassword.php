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
use Magento\Customer\Model\AccountManagement;

class ForgotPassword implements ResolverInterface
{
		const EMAIL_RESET_PWA = 'email_reset_pwa';


    public function __construct(
        \Magento\Customer\Api\AccountManagementInterface $accountManagementInterface
    ){
        $this->_customerAccountManagement = $accountManagementInterface;
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
            $email = $this->validateEmail($args);
            
            $this->_customerAccountManagement->initiatePasswordReset(
                $email,
                'email_reset_pwa'
            );
            return [
                "message" => __('If there is an account associated with %1 you will receive an email with a link to reset your password.', $email)
            ];
        } catch (NoSuchEntityException $exception){
            throw new GraphQlNoSuchEntityException(__('%1 account not found.', $email));
        } catch (\Exception $exception) {
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
