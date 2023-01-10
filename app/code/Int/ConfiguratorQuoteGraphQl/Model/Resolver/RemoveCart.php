<?php
/**
 * Indusnet Technologies.
 *
 * @category  Indusnet
 * @package   Int_ConfiguratorGraphQl
 * @author    Indusnet
 */
namespace Int\ConfiguratorQuoteGraphQl\Model\Resolver;

use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Exception\GraphQlAuthorizationException;
use Magento\Framework\GraphQl\Exception\GraphQlNoSuchEntityException;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\GraphQl\Model\Query\ContextInterface;

/**
 * Class RemoveCart
 * @package Int\ConfiguratorQuoteGraphQl\Model\Resolver
 */
class RemoveCart implements ResolverInterface
{
    protected $quoteIdMaskFactory;
    protected $quoteFactory;
    protected $quoteItem;

    public function __construct(
        \Magento\Quote\Model\QuoteIdMaskFactory $quoteIdMaskFactory,
        \Magento\Quote\Model\QuoteFactory $quoteFactory,
        \Magento\Quote\Model\Quote\Item $quoteItem
    ) {
        $this->quoteIdMaskFactory = $quoteIdMaskFactory;
        $this->quoteFactory = $quoteFactory;
        $this->quoteItem = $quoteItem;
    }

    /**
     * @inheritdoc
     */
    public function resolve(Field $field, $context, ResolveInfo $info, array $value = null, array $args = null)
    {
        if (empty($args['input']['cart_id'])) {
            throw new GraphQlInputException(__('Required parameter "cart_id" is missing'));
        }

        $maskedCartId = $args['input']['cart_id'];

        try {
            $quoteIdMask = $this->quoteIdMaskFactory->create()->load($maskedCartId, 'masked_id');
            $quoteId = $quoteIdMask->getQuoteId();

            $quote = $this->quoteFactory->create()->load($quoteId);
            $quoteItems = $quote->getAllItems();

            foreach($quoteItems as $item)
            {
                $quoteItem = $this->quoteItem->load($item->getId());
                $quoteItem->delete();
            }
        } catch (NoSuchEntityException $e) {
            throw new GraphQlNoSuchEntityException(__($e->getMessage()), $e);
        }

        $info = array('status' => 1, 'message' => __('Cart items remove successfully.'));

        return $info;
    }
}