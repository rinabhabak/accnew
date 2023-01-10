<?php
/**
 * Indusnet Technologies.
 *
 * @category  Indusnet
 * @package   Int_OrderDetailsGraphQl
 * @author    Indusnet
 */
namespace Int\OrderDetailsGraphQl\Model\Resolver;

use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;

class ShipTo implements ResolverInterface
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

        try{
            $order = $this->order->load($value['id']);
            if($order){
                $shippingAddress = $this->order->getShippingAddress();
                return $shippingAddress->getFirstname().' '.$shippingAddress->getLastname();
            }else{
                return '';
            }
        } catch (\Exception $e) {
            throw new GraphQlInputException(
                __($e->getMessage())
            );
        }



    }

}
