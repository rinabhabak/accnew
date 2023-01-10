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

/**
 * Sales Order field resolver, used for GraphQL request processing
 */
class CustomerInvitation implements ResolverInterface
{
    public function __construct(
        \Magento\Invitation\Model\InvitationFactory $invitationFactory,
        \Magento\Invitation\Helper\Data $invitationStatus
    ) {
        $this->_invitationFactory = $invitationFactory;
        $this->_invitationStatus = $invitationStatus;
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
        /** @var ContextInterface $context */
        if (false === $context->getExtensionAttributes()->getIsCustomer()) {
            throw new GraphQlAuthorizationException(__('The current customer isn\'t authorized.'));
        }

        $invitationData = $this->getInvitationData($context->getUserId());
        return $invitationData;
    }

    /**
     * @param int $customer_id
     * @return array
     * @throws GraphQlNoSuchEntityException
     */
    private function getInvitationData($customer_id): array
    {
        $pageData = [];

        try {
            $invitationCollection = $this->_invitationFactory->create()->getCollection()
            ->addOrder('invitation_id', \Magento\Framework\Data\Collection::SORT_ORDER_DESC)
            ->loadByCustomerId($customer_id);

            foreach($invitationCollection as $invitation){
                $pageData[] = [
                    "invitation_id" => $invitation->getId(),
                    "email" => $invitation->getEmail(),
                    "status" => $this->getStatusText($invitation),
                    "invitation_date" => $invitation->getInvitationDate(),
                ];
            }
        } catch (NoSuchEntityException $e) {
            throw new GraphQlNoSuchEntityException(__($e->getMessage()), $e);
        }

        return $pageData;
    }

    /**
     * Return status text for invitation
     */
    private function getStatusText($invitation)
    {
        return $this->_invitationStatus->getInvitationStatusText($invitation);
    }
}