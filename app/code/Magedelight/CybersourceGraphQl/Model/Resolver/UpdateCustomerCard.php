<?php
/**
 * Magedelight
 *
 * @category Magedelight
 * @package  Magedelight_CybersourceGraphQl
 * @author   Magedelight <info@magedelight.com>
 * @license  http://opensource.org/licenses/gpl-3.0.html GNU General Public License,version 3 (GPL-3.0)
 * @copyright Copyright (c) 2020 Magedelight (http://www.magedelight.com)
 */

declare(strict_types=1);

namespace Magedelight\CybersourceGraphQl\Model\Resolver;

use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magedelight\CybersourceGraphQl\Model\UpdateCustomerCardService;
use Magento\Framework\GraphQl\Exception\GraphQlAuthorizationException;

/**
 * Class UpdateCustomerCard
 * @package Magedelight\CybersourceGraphQl\Model\Resolver
 */
class UpdateCustomerCard implements ResolverInterface
{
    /**
     * @var UpdateCustomerCardService
     */
    protected $updateCustomerCardsService;

    /**
     * UpdateCustomerCard constructor.
     * @param UpdateCustomerCardService $updateCustomerCardsService
     */
    public function __construct(
        UpdateCustomerCardService $updateCustomerCardsService
    ) {
        $this->updateCustomerCardsService = $updateCustomerCardsService;
    }

    public function resolve(Field $field, $context, ResolveInfo $info, array $value = null, array $args = null)
    {
        if (false === $context->getExtensionAttributes()->getIsCustomer()) {
            throw new GraphQlAuthorizationException(__('The current customer isn\'t authorized.'));
        }
        if (empty($args['input']) || !is_array($args['input'])) {
            throw new GraphQlInputException(__('"input" value should be specified'));
        }
        $response = $this->updateCustomerCardsService->execute($args['input'],$context->getUserId());
        $resultflag = ($response['status']==1)? true : false;
        return ['result' => $resultflag];
    }
}