<?php
namespace Int\CybersourceGraphQl\Model\Resolver;

use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Exception\GraphQlNoSuchEntityException;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Framework\GraphQl\Exception\GraphQlAuthorizationException;

class SavedCards implements ResolverInterface
{

    private $savedCardsDataProvider;

    /**
     * @param DataProvider\SavedCards $savedCardsRepository
     */
    public function __construct(
        \Int\CybersourceGraphQl\Model\Resolver\DataProvider\SavedCards $savedCardsDataProvider
    ) {
        $this->savedCardsDataProvider = $savedCardsDataProvider;
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

        $customerId = $this->getCustomerId($context->getUserId());
        
        $savedCardsData = $this->savedCardsDataProvider->getSavedCards($customerId);
        return $savedCardsData;
    }

    /**
     * @param string $customer_id
     * @return int
     * @throws GraphQlInputException
     */
    private function getCustomerId(string $customer_id): int
    {
        if (!isset($customer_id)) {
            throw new GraphQlInputException(__('Customer Id should be specified'));
        }

        return (int)$customer_id;
    }
}
