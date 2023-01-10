<?php
/**
 * Indusnet Technologies.
 *
 * @category  Indusnet
 * @package   Int_CheckoutGraphQl
 * @author    Indusnet
 */

namespace Int\CheckoutGraphQl\Model\Resolver;

use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Exception\GraphQlNoSuchEntityException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Catalog\Api\Data\ProductInterface;


use Magento\Quote\Api\Data\CartInterface;
use Magento\Framework\GraphQl\Query\Resolver\ContextInterface;
use Magento\Quote\Api\CartManagementInterface;
use Magento\Sales\Api\Data\OrderInterfaceFactory;
/**
 * Class RegionId
 * @package Int\CheckoutGraphQl\Model\Resolver
 */
class RegionId implements ResolverInterface
{

    protected $orderFactory;
    /**
     * @var \Magento\Sales\Model\Order\Email\Sender\OrderSender
     */
    protected $orderSender;
    protected $_cart;
    protected $test;
    private $cartManagement;

    /**
     * @param OrderInterfaceFactory $orderFactory
     * @param CartInterface $cart
     * @param CartManagementInterface $cartManagement
     */
    public function __construct(
        OrderInterfaceFactory $orderFactory,
        CartInterface $cart,
        CartManagementInterface $cartManagement
    ) {
        $this->orderFactory = $orderFactory;
        $this->_cart = $cart;
        $this->cartManagement = $cartManagement;
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
        
        if (!isset($value['region_id'])) {
            throw new GraphQlInputException(__('"region_id" is require.'));
        }

        if(isset($value['region_id'])){
            $regionId = $value['region_id'];
        }
        
        return $regionId;

    }
}