<?php
declare(strict_types=1);

namespace Int\InvitationGraphQl\Model\Resolver;

use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlAuthorizationException;
use Magento\Framework\GraphQl\Exception\GraphQlNoSuchEntityException;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\GraphQl\Model\Query\ContextInterface;
use Magento\Framework\GraphQl\Exception\GraphQlAlreadyExistsException;

class SendInvitation implements ResolverInterface
{
    /**
     * @var \Magento\Customer\Model\CustomerFactory
     */
    protected $_customerFactory;

    /**
     * @var \Magento\Invitation\Model\InvitationFactory
     */
    protected $_invitationFactory;

    /**
     * @var \Magento\Invitation\Model\Config
     */
    protected $_config;

    /**
     * @var \Magento\Framework\Escaper
     */
    protected $_escaper;

    /**
     * @param \Magento\Customer\Model\CustomerFactory $customerFactory
     * @param \Magento\Invitation\Model\Config $config
     * @param \Magento\Invitation\Model\InvitationFactory $invitationFactory
     * @param \Magento\Framework\Escaper $escaper
     */
    public function __construct(
        \Magento\Customer\Model\CustomerFactory $customerFactory,
        \Magento\Invitation\Model\Config $config,
        \Magento\Invitation\Model\InvitationFactory $invitationFactory,
        \Magento\Framework\Escaper $escaper
    ){
        $this->_customerFactory = $customerFactory;
        $this->_config = $config;
        $this->_invitationFactory = $invitationFactory;
        $this->_escaper = $escaper;
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

        /** @var ContextInterface $context */
        if (false === $context->getExtensionAttributes()->getIsCustomer()) {
            throw new GraphQlAuthorizationException(__('The current customer isn\'t authorized.'));
        }

        $customer = $this->getCustomerData($context->getUserId());
        
        $message = isset($args['message']) ? $args['message'] : '';
        if (!$this->_config->isInvitationMessageAllowed()) {
            $message = '';
        }
        
        $invPerSend = $this->_config->getMaxInvitationsPerSend();
        $attempts = 0;
        $customerExists = 0;

        foreach ($args['email'] as $email) {
            $attempts++;
            if (!\Zend_Validate::is($email, 'EmailAddress')) {
                continue;
            }
            if ($attempts > $invPerSend) {
                continue;
            }
            try {
                /** @var Invitation $invitation */
                $invitation = $this->_invitationFactory->create();
                $invitation->setData(
                    ['email' => $email, 'customer' => $customer, 'message' => $message]
                )->save();
                if ($invitation->sendPwaInvitationEmail()) {
                    $response['message']['success'] = __('You sent the invitation for %1.', $this->_escaper->escapeHtml($email));
                } else {
                    $response['message']['error'] = __('Something went wrong while sending an email to %1.', $this->_escaper->escapeHtml($email));
                }

            } catch (\Magento\Framework\Exception\AlreadyExistsException $e) {
                $customerExists++;
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $response['message']['error'] = __($e->getMessage());
            } catch (\Exception $e) {
                $response['message']['error'] = __($e->getMessage());
            }
        }
        if ($customerExists) {
            $response['message']['notice'] = __(
                'We did not send %1 invitation(s) addressed to current customers.',
                $customerExists
            );
        }


        return $response;
    }

    /**
     * Return customer data 
     */
    private function getCustomerData(int $customer_id)
    {
        return $this->_customerFactory->create()->load($customer_id);
    } 
}
