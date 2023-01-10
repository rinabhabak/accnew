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

use Magedelight\Cybersource\Api\CybersourceTokenRepositoryInterface;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magedelight\Cybersource\Api\CybersourceTokenRepositoryInterfaceFactory;
use Magento\Framework\GraphQl\Exception\GraphQlAuthorizationException;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;

/**
 * Class CybersourceCard
 * @package Magedelight\CybersourceGraphQl\Model\Resolver
 */
class CybersourceCard implements ResolverInterface
{
    /**
     * @var CybersourceTokenRepositoryInterfaceFactory
     */
    private $cybersourceTokenRepositoryInterfaceFactory;

    /**
     * CybersourceCard constructor.
     * @param CybersourceTokenRepositoryInterfaceFactory $cybersourceTokenRepositoryInterfaceFactory
     */
    public function __construct(
        CybersourceTokenRepositoryInterfaceFactory $cybersourceTokenRepositoryInterfaceFactory)
    {
        $this->cybersourceTokenRepositoryInterfaceFactory = $cybersourceTokenRepositoryInterfaceFactory;
    }

    /**
     * @inheritdoc
     */
    public function resolve(Field $field, $context, ResolveInfo $info, array $value = null, array $args = null)
    {
        if (false === $context->getExtensionAttributes()->getIsCustomer()) {
            throw new GraphQlAuthorizationException(__('The current customer isn\'t authorized.'));
        }
        if (empty($args['input']) || !is_array($args['input'])) {
            throw new GraphQlInputException(__('"input" value should be specified'));
        }
        if (!isset($args['input']['id'])) {
            throw new GraphQlInputException(
                __('Required parameter "id" for "cybersource tokenization" is missing.')
            );
        }
        $cybersourceTokenRepository = $this->cybersourceTokenRepositoryInterfaceFactory->create();
        $searchResult = $cybersourceTokenRepository->getById($args['input']['id']);
        return [
            'item' => $searchResult,
        ];
    }
}