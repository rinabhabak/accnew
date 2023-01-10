<?php
/**
 * Indusnet Technologies.
 *
 * @category  Indusnet
 * @package   Int_OrderDetailsGraphQl
 * @author    Indusnet
 */
namespace Int\OrderDetailsGraphQl\Model\Resolver;

use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Exception\GraphQlNoSuchEntityException;
use Magento\Framework\GraphQl\Exception\GraphQlAuthorizationException;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Framework\GraphQl\Query\Resolver\ContextInterface;
use Magento\Framework\GraphQl\Query\Resolver\Value;
use Magento\Framework\GraphQl\Query\Resolver\ValueFactory;
use Magento\Authorization\Model\UserContextInterface;

class ConfiguratorPid implements ResolverInterface
{
    protected $order;

    public function __construct(
        \Magento\Sales\Api\Data\OrderInterface $order
    )
    {
        $this->order = $order;
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
        $order = $this->order->load($value['id']);
        
        try{
            $configuratorPid = $this->order->getConfiguratorPid();
        } catch (\Exception $e) {
            throw new GraphQlInputException(
                __($e->getMessage())
            );
        }

        return $configuratorPid;
    }
}