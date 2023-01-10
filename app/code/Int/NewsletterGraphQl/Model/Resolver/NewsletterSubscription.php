<?php


namespace Int\NewsletterGraphQl\Model\Resolver;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Exception\GraphQlNoSuchEntityException;
use Magento\Framework\GraphQl\Exception\GraphQlAlreadyExistsException;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Framework\App\ObjectManager;
use Magento\Store\Model\ScopeInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Phrase;
use Magento\Newsletter\Model\Subscriber;
use Magento\Newsletter\Model\SubscriberFactory;
use Magento\Customer\Api\AccountManagementInterface as CustomerAccountManagement;
use Magento\Framework\Validator\EmailAddress as EmailValidator;
use Magento\Customer\Model\Url as CustomerUrl;
use Magento\GraphQl\Model\Query\ContextInterface;

class NewsletterSubscription implements ResolverInterface
{

    /**
     * Initialize dependencies.
     * @param SubscriberFactory $subscriberFactory
     * @param CustomerUrl $customerUrl
     * @param CustomerAccountManagement $customerAccountManagement
     * @param EmailValidator $emailValidator
     */
    public function __construct(
        SubscriberFactory $subscriberFactory,
        CustomerUrl $customerUrl,
        CustomerAccountManagement $customerAccountManagement,
        EmailValidator $emailValidator
    ) {
        $this->customerAccountManagement = $customerAccountManagement;
        $this->_subscriberFactory = $subscriberFactory;
        $this->_customerUrl = $customerUrl;
        $this->emailValidator = $emailValidator;
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
    ) {
        $response = [];
        $customerExists = 0;
        $email = $this->getEmailAddress($args);

        /** @var ContextInterface $context */
        $websiteId = (int) $context->getExtensionAttributes()->getStore()->getWebsiteId();

        try {
            $this->validateEmailFormat($email);
            $this->validateGuestSubscription();
            $this->validateEmailAvailable($email, $websiteId);

            $subscriber = $this->_subscriberFactory->create()->loadByEmail($email);
            
            if ($subscriber->getId() && (int) $subscriber->getSubscriberStatus() === Subscriber::STATUS_SUBSCRIBED){
                throw new GraphQlAlreadyExistsException(
                    __('That email address is already subscribed.')
                );
            }

            $status = (int) $this->_subscriberFactory->create()->subscribe($email);
            
            if ($status === 1) {
                $response['message']['success'] = __('Thank you for your subscription.');
            }

        } catch (\Magento\Framework\Exception\AlreadyExistsException $e) {
            $response['message']['notice'] = __('That email address is already subscribed.');
        }  catch (GraphQlInputException $e) {
            $response['message']['error'] = __($e->getMessage());
        } catch (\Exception $e) {
            $response['message']['error'] = __($e->getMessage());
        }

        return $response;
    }

    /**
     * Validates that the email address isn't being used by a different account.
     *
     * @param string $email
     * @param string $websiteId
     * @throws GraphQlNoSuchEntityException
     * @return void
     */
    protected function validateEmailAvailable($email, $websiteId)
    {
        if ( !$this->customerAccountManagement->isEmailAvailable($email, $websiteId) ) 
        {
            throw new GraphQlNoSuchEntityException(
                __('That email address is already subscribed.')
            );
        }
    }

    /**
     * Validates that if the current user is a guest, that they can subscribe to a newsletter.
     *
     * @throws GraphQlNoSuchEntityException
     * @return void
     */
    private function validateGuestSubscription()
    {
        if (ObjectManager::getInstance()->get(ScopeConfigInterface::class)
                ->getValue(
                    Subscriber::XML_PATH_ALLOW_GUEST_SUBSCRIBE_FLAG,
                    ScopeInterface::SCOPE_STORE
                ) != 1
        ) {
            throw new GraphQlNoSuchEntityException(
                __(
                    'Sorry, but the administrator denied subscription for guests. Please <a href="%1">register</a>.',
                    $this->_customerUrl->getRegisterUrl()
                )
            );
        }
    }

    /**
     * Validates the format of the email address
     *
     * @param string $email
     * @throws GraphQlInputException
     * @return void
     */
    private function validateEmailFormat($email)
    {
        if (!$this->emailValidator->isValid($email)) {
            throw new GraphQlInputException(__('Please enter a valid email address.'));
        }
    }

    /**
     * Get success message
     *
     * @param int $status
     * @return Phrase
     */
    private function getSuccessMessage(int $status): Phrase
    {
        if ($status === Subscriber::STATUS_NOT_ACTIVE) {
            return __('The confirmation request has been sent.');
        }

        return __('Thank you for your subscription.');
    }

    /**
     * @param array $args
     * @return int
     * @throws GraphQlInputException
     */
    private function getEmailAddress(array $args): string
    {
        if (!isset($args['email']) || empty($args['email'])) {
            throw new GraphQlInputException(
                __('Email id should be specified')
            );
        }

        return (string) $args['email'];
    }

}
