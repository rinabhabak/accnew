<?php
/**
 * Magedelight
 *
 * @category Magedelight
 * @package  Magedelight_CybersourcesopGraphQl
 * @author   Magedelight <info@magedelight.com>
 * @license  http://opensource.org/licenses/gpl-3.0.html GNU General Public License,version 3 (GPL-3.0)
 * @copyright Copyright (c) 2020 Magedelight (http://www.magedelight.com)
 */
declare(strict_types=1);

namespace Magedelight\CybersourcesopGraphQl\Model\Resolver;

use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Framework\GraphQl\Exception\GraphQlAuthorizationException;
use Magento\Payment\Gateway\Command\CommandPoolInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\GraphQl\Exception\GraphQlNoSuchEntityException;
use Magento\Quote\Model\MaskedQuoteIdToQuoteIdInterface;
use Magento\Payment\Gateway\Data\PaymentDataObjectFactory;
use Magento\Quote\Api\PaymentMethodManagementInterface;

/**
 * Class TokenRequest
 * @package Magedelight\CybersourceGraphQl\Model\Resolver
 */
class TokenRequest implements ResolverInterface
{
    const TOKEN_COMMAND_NAME = 'TokenCreateCommand';
    
    /**
     * @var CommandPoolInterface
     */
    private $commandPool;
    
    /**
     * @var MaskedQuoteIdToQuoteIdInterface
     */
    private $maskedQuoteIdToQuoteId;
    
    /**
     * @var PaymentMethodManagementInterface
     */
    private $paymentMethodManagement;
    
    /**
     * @var PaymentDataObjectFactory
     */
    private $paymentDataObjectFactory;
    
    /**
     * 
     * @param CommandPoolInterface $commandPool
     * @param MaskedQuoteIdToQuoteIdInterface $maskedQuoteIdToQuoteId
     * @param PaymentMethodManagementInterface $paymentMethodManagement
     * @param PaymentDataObjectFactory $paymentDataObjectFactory
     */
    public function __construct(
        CommandPoolInterface $commandPool,
        MaskedQuoteIdToQuoteIdInterface $maskedQuoteIdToQuoteId,
        PaymentMethodManagementInterface $paymentMethodManagement,
        PaymentDataObjectFactory $paymentDataObjectFactory    
        )
    {
        $this->commandPool = $commandPool;
        $this->maskedQuoteIdToQuoteId = $maskedQuoteIdToQuoteId;
        $this->paymentMethodManagement = $paymentMethodManagement;
        $this->paymentDataObjectFactory = $paymentDataObjectFactory;
    }

    /**
     * @inheritdoc
     */
    public function resolve(Field $field, $context, ResolveInfo $info, array $value = null, array $args = null)
    {
        $this->vaildateArgs($args);
        $arguments = [
            'amount' => 0,
            'cc_type' => $args['cc_type']
        ];
         try {
            $command = $this->commandPool->get(self::TOKEN_COMMAND_NAME);
            $maskedCartId = $args['cart_id'];
            try {
                $cartId = $this->maskedQuoteIdToQuoteId->execute($maskedCartId);
            } catch (NoSuchEntityException $exception) {
                throw new GraphQlNoSuchEntityException(
                    __('Could not find a cart with ID "%masked_cart_id"', ['masked_cart_id' => $maskedCartId])
                );
            }
            $payment = $this->paymentMethodManagement->get(
                $cartId
            );
            $arguments['payment'] = $this->paymentDataObjectFactory->create($payment);
            $commandResult = $command->execute($arguments);
            $result = $commandResult->get();
            return $result;
        } catch (\Exception $e) {
           // throw new GraphQlInputException(__('Payment Token Build Error.'));
            throw new GraphQlInputException(__($e->getMessage()));
        }
    }

    /**
     * @param array $args
     * @throws GraphQlInputException
     */
    private function vaildateArgs(array $args): void
    {
        if (!isset($args['cart_id'])) {
            throw new GraphQlInputException(__('cart Id is required field.'));
        }
        if (!isset($args['cc_type'])) {
            throw new GraphQlInputException(__('cc type is required field.'));
        }
    }
}