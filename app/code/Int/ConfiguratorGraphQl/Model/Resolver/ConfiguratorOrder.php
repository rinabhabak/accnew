<?php
/**
 * Indusnet Technologies.
 *
 * @category  Indusnet
 * @package   Int_ConfiguratorGraphQl
 * @author    Indusnet
 */

namespace Int\ConfiguratorGraphQl\Model\Resolver;

use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Exception\GraphQlAuthorizationException;
use Magento\Framework\GraphQl\Exception\GraphQlNoSuchEntityException;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\GraphQl\Model\Query\ContextInterface;

/**
 * Class ConfiguratorOrder
 * @package Int\ConfiguratorGraphQl\Model\Resolver
 */
class ConfiguratorOrder implements ResolverInterface
{
    protected $_orderCollectionFactory;
    protected $_configuratorOrderDataProvider;

    public function __construct(
        \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $orderCollectionFactory,
        \Int\ConfiguratorGraphQl\Model\Resolver\DataProvider\ConfiguratorOrder $configuratorOrderDataProvider
    ) {
        $this->_orderCollectionFactory = $orderCollectionFactory;
        $this->_configuratorOrderDataProvider = $configuratorOrderDataProvider;
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
        $configuratorPid = $this->getConfiguratorPid($args);
        $orderInfo = $this->getOrderData($configuratorPid);
        $configuratorDetails = $this->_configuratorOrderDataProvider->getConfigurator($configuratorPid);
        $orderInfo['configuratorDetails'][0] = $configuratorDetails;

        return $orderInfo;
    }

    /**
     * @param array $args
     * @return int
     * @throws GraphQlInputException
     */
    private function getConfiguratorPid(array $args)
    {
        if (!isset($args['configurator_pid'])) {
            throw new GraphQlInputException(__('"Configurator project id should be specified'));
        }

        return $args['configurator_pid'];
    }

    /**
     * @param string configuratorPid
     * @return array
     * @throws GraphQlNoSuchEntityException
     */
    private function getOrderData($configuratorPid): array
    {
        try {
            $collection = $this->_orderCollectionFactory->create()
                               ->addAttributeToSelect('*')
                               ->addFieldToFilter('configurator_pid', array('like' => '%'.$configuratorPid.'%'))
                               ->setOrder('created_at','DESC');

            $orderInfo = array();

            foreach ($collection as $order) {
                $itemData = array();

                $itemsData['configurator_pid'] = $configuratorPid;

                foreach ($order->getAllVisibleItems() as $_item) {
                    $itemsData['order_items'][] = $_item->getData();
                }

                $orderInfo['configuratorOrderRecords'][] = [
                    'order_id' => $order->getId(),
                    'increment_id' => $order->getIncrementId(),
                    'configurator_pid' => $configuratorPid,
                    'shipping_amount' => $order->getShippingAmount(),
                    'tax_amount' => $order->getTaxAmount(),
                    'grand_total' => $order->getGrandTotal(),
                    'customer_name' => $order->getCustomerFirstname().' '.$order->getCustomerLastname(),
                    'created_at' => date('F j, Y',strtotime($order->getCreatedAt())),
                    'is_guest_customer' => !empty($order->getCustomerIsGuest()) ? 1 : 0,
                    'shipping_method' => $order->getShippingMethod(),
                    'order_status' => $order->getStatus(),
                    'items' => $itemsData
                ];
            }
        } catch (NoSuchEntityException $e) {
            throw new GraphQlNoSuchEntityException(__($e->getMessage()), $e);
        }

        return $orderInfo;
    }
}