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
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Query\Resolver\Argument\SearchCriteria\Builder as SearchCriteriaBuilder;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magedelight\Cybersource\Api\CardManagementInterfaceFactory;
use Magento\Framework\GraphQl\Exception\GraphQlAuthorizationException;
use Magento\Framework\Encryption\EncryptorInterface;

/**
 * Class CybersourceCardList
 * @package Magedelight\CybersourceGraphQl\Model\Resolver
 */
class CybersourceCardList implements ResolverInterface
{
    /**
     * @var CybersourceTokenRepositoryInterface
     */
    private $cybersourceTokenRepository;

    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @var CardManagementInterfaceFactory
     */
    private $cardManagementInterfaceFactory;

    /**
     * @var EncryptorInterface
     */
    private $encryptor;

    /**
     * CybersourceCardList constructor.
     * @param CybersourceTokenRepositoryInterface $cybersourceTokenRepository
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param CardManagementInterfaceFactory $cardManagementInterfaceFactory
     */
    public function __construct(
        CybersourceTokenRepositoryInterface $cybersourceTokenRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        CardManagementInterfaceFactory $cardManagementInterfaceFactory,
        EncryptorInterface $encryptor)
    {
        $this->cybersourceTokenRepository = $cybersourceTokenRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->cardManagementInterfaceFactory = $cardManagementInterfaceFactory;
        $this->encryptor = $encryptor;
    }

    /**
     * @inheritdoc
     */
    public function resolve(Field $field, $context, ResolveInfo $info, array $value = null, array $args = null)
    {
        if (false === $context->getExtensionAttributes()->getIsCustomer()) {
            throw new GraphQlAuthorizationException(__('The current customer isn\'t authorized.'));
        }

        $this->vaildateArgs($args);
        $cardManagement = $this->cardManagementInterfaceFactory->create();
        $searchResult = $cardManagement->getCardListing($context->getUserId());
        foreach ($searchResult as $key=>$singleresult){
            $singleresult["subscription_id"] = $this->encryptor->encrypt($singleresult["subscription_id"]);
            $searchResult[$key] = $singleresult;
        }
        return [
            'items' => $searchResult,
        ];
    }

    /**
     * @param array $args
     * @throws GraphQlInputException
     */
    private function vaildateArgs(array $args): void
    {
        if (isset($args['currentPage']) && $args['currentPage'] < 1) {
            throw new GraphQlInputException(__('currentPage value must be greater than 0.'));
        }
        if (isset($args['pageSize']) && $args['pageSize'] < 1) {
            throw new GraphQlInputException(__('pageSize value must be greater than 0.'));
        }

    }
}