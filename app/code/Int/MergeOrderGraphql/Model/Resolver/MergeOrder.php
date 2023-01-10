<?php
/**
 * Copyright © Indusnet Technologies All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Int\MergeOrderGraphql\Model\Resolver;

use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Exception\GraphQlNoSuchEntityException;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;

class MergeOrder implements ResolverInterface
{
    /**
     * @var \Magento\Sales\Api\OrderRepositoryInterface
     */
    protected $orderRepository;

    /**
     * @var \Magento\Framework\Api\SearchCriteriaBuilder
     */
    protected $searchCriteriaBuilder;

    /**
     * @var \Magento\Customer\Model\AddressFactory
     */
    protected $addressFactory;

    /**
     * @param \Magento\Sales\Api\OrderRepositoryInterface $orderRepository
     * @param \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder
     * @param \Magento\Customer\Model\AddressFactory $addressFactory
     */
    public function __construct(
        \Magento\Sales\Api\OrderRepositoryInterface $orderRepository,
        \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder,
        \Magento\Customer\Model\AddressFactory $addressFactory
    ) {
        $this->orderRepository = $orderRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->addressFactory = $addressFactory;
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
        if (!isset($args['input']['order_id']) || $args['input']['order_id'] < 1 ) {
            throw new GraphQlInputException(__('Order id should be specified'));
        }

        if (!isset($args['input']['customer_id']) || $args['input']['customer_id'] < 1 ) {
            throw new GraphQlInputException(__('Customer id should be specified'));
        }

        try{

            $incrementId = $args['input']['order_id'];
            $customerId = $args['input']['customer_id'];

            $searchCriteria = $this->searchCriteriaBuilder->addFilter('increment_id', $incrementId, 'eq')->create();
            $order = $this->orderRepository->getList($searchCriteria)->getFirstItem();
            
            if(!$order->getId()) {
                throw new GraphQlInputException(__('#%1 Order not exists', $incrementId));
            }

            /* Check Customer Id not assigned with the order */
            if(!$order->getCustomerId() && is_null($order->getCustomerId()))
            {
                /* Assign Customer Id with Order Id */
                $order->setCustomerId($customerId);
                $order->setCustomerIsGuest(0);
                $this->orderRepository->save($order);

                /* Save billing Address */
                $address = $this->addressFactory->create();
                $address->setData($order->getBillingAddress()->getData());

                $address->setCustomerId($customerId)
                    ->setIsDefaultBilling('1')
                    ->setIsDefaultShipping('0')
                    ->setSaveInAddressBook('1');
                $address->save();

                /* Save shipping Address */
                if (!$order->getIsVirtual()) {
                    $address = $this->addressFactory->create();
                    $address->setData($order->getShippingAddress()->getData());

                    $address->setCustomerId($customerId)
                        ->setIsDefaultBilling('0')
                        ->setIsDefaultShipping('1')
                        ->setSaveInAddressBook('1');
                    $address->save();
                }

                return [
                    'message' => __('Congratulations! #%1 is merged with your account.', $incrementId)
                ];

            }  else  {
                return [
                    'message' => __('Oops! #%1 already merged with your account.', $incrementId)
                ];
            }
            
        } catch (\Exception $e) {
            throw new GraphQlInputException(__($e->getMessage()));
        }
    }
}

