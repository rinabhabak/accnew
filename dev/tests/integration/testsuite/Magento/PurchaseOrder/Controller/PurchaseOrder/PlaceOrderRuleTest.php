<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\PurchaseOrder\Controller\PurchaseOrder;

use Magento\Customer\Model\Session;
use Magento\PurchaseOrder\Api\Data\PurchaseOrderInterface;
use Magento\PurchaseOrder\Api\PurchaseOrderRepositoryInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\Mail\Template\TransportBuilderMock;
use Magento\Framework\Message\MessageInterface;
use Magento\SalesRule\Api\RuleRepositoryInterface;
use Magento\CatalogRule\Api\CatalogRuleRepositoryInterface;
use Magento\CatalogRule\Model\ResourceModel\Rule\CollectionFactory as CatalogRuleCollectionFactory;
use Magento\CatalogRule\Model\Indexer\IndexBuilder as CatalogRuleIndexBuilder;
use Magento\CatalogRule\Api\Data\RuleInterface;

/**
 * Controller test class for the purchase order place order as company admin with price rules.
 *
 * @magentoAppArea frontend
 * @magentoAppIsolation enabled
 * @see \Magento\PurchaseOrder\Controller\PurchaseOrder\PlaceOrder
 */
class PlaceOrderRuleTest extends PurchaseOrderAbstract
{
    /**
     * Url to dispatch.
     */
    private const URI = 'purchaseorder/purchaseorder/placeorder';

    /**
     * @var Session
     */
    private $session;

    /**
     * @var CatalogRuleCollectionFactory
     */
    private $catalogRuleCollectionFactory;

    /**
     * @var CatalogRuleIndexBuilder
     */
    private $catalogRuleIndexBuilder;

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->objectManager = Bootstrap::getObjectManager();
        $this->session = $this->objectManager->get(Session::class);
        $this->catalogRuleCollectionFactory = $this->objectManager->get(CatalogRuleCollectionFactory::class);
        $this->catalogRuleIndexBuilder = $this->objectManager->get(CatalogRuleIndexBuilder::class);
    }

    /**
     * Verify a purchase place order totals with cart price rule with coupon
     *
     * @param string $status
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     * @throws \Magento\Framework\Exception\InputException
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @dataProvider convertablePurchaseOrderStatusDataProvider
     * @magentoDataFixture Magento/PurchaseOrder/_files/purchase_orders_with_coupon_applied.php
     */
    public function testPlaceOrderActionAsCompanyAdminApprovedPurchaseOrderWithCartPriceRuleCoupon($status)
    {
        $purchaseOrder = $this->getPurchaseOrder('admin@magento.com', 'customer@example.com', $status);
        $purchaseOrder->setStatus($status);
        $purchaseOrderRepository = $this->objectManager->get(PurchaseOrderRepositoryInterface::class);
        $purchaseOrderRepository->save($purchaseOrder);
        self::assertNull($purchaseOrder->getOrderId());
        self::assertNull($purchaseOrder->getOrderIncrementId());
        $id = $purchaseOrder->getEntityId();
        $this->dispatch(self::URI . '/request_id/' . $id);

        // assert result
        $postPurchaseOrder = $purchaseOrderRepository->getById($id);
        self::assertEquals(PurchaseOrderInterface::STATUS_ORDER_PLACED, $postPurchaseOrder->getStatus());
        self::assertNotNull($postPurchaseOrder->getOrderId());
        self::assertNotNull($postPurchaseOrder->getOrderIncrementId());
        $this->assertSessionMessages($this->isEmpty(), MessageInterface::TYPE_ERROR);
        $successMessage = 'Successfully placed order #test_order_1 from purchase order #'
            . $postPurchaseOrder->getIncrementId()
            . '.';
        $this->assertSessionMessages(
            self::equalTo([(string)__($successMessage)]),
            MessageInterface::TYPE_SUCCESS
        );

        $order = $this->objectManager->get(OrderRepositoryInterface::class)->get($postPurchaseOrder->getOrderId());
        self::assertEquals($order->getIncrementId(), $postPurchaseOrder->getOrderIncrementId());
        self::assertEquals($order->getGrandTotal(), $purchaseOrder->getSnapshotQuote()->getGrandTotal());
        $this->session->logout();

        // Assert email notification
        /** @var TransportBuilderMock $transportBuilderMock */
        $transportBuilderMock = $this->objectManager->get(TransportBuilderMock::class);
        $sentMessage = $transportBuilderMock->getSentMessage();
        self::assertStringContainsString('order confirmation', $sentMessage->getSubject());
        self::assertStringContainsString(
            'Thank you for your order from ',
            $sentMessage->getBody()->getParts()[0]->getRawContent()
        );
        self::assertStringContainsString(
            "Your Order <span class=\"no-link\">#test_order_1</span>",
            $sentMessage->getBody()->getParts()[0]->getRawContent()
        );
    }

    /**
     * Verify a purchase place order totals with removed cart price rule rate with coupon
     *
     * @param string $status
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     * @throws \Magento\Framework\Exception\InputException
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @dataProvider convertablePurchaseOrderStatusDataProvider
     * @magentoDataFixture Magento/PurchaseOrder/_files/purchase_orders_with_coupon_applied.php
     */
    public function testPlaceOrderActionAsCompanyAdminApprovedPurchaseOrderOrderWithCartPriceRuleCouponRemove($status)
    {
        $purchaseOrder = $this->getPurchaseOrder('admin@magento.com', 'customer@example.com', $status);
        $id = $purchaseOrder->getEntityId();
        //remove applied rule
        $ruleRepository = $this->objectManager->get(RuleRepositoryInterface::class);
        $appliedRule = $ruleRepository->getById($purchaseOrder->getSnapshotQuote()->getAppliedRuleIds());
        $ruleRepository->deleteById($appliedRule->getRuleId());
        $this->dispatch(self::URI . '/request_id/' . $id);
        // assert result
        $postPurchaseOrder = $this->objectManager->get(PurchaseOrderRepositoryInterface::class)->getById($id);
        self::assertEquals(PurchaseOrderInterface::STATUS_ORDER_PLACED, $postPurchaseOrder->getStatus());
        self::assertNotNull($postPurchaseOrder->getOrderId());
        self::assertNotNull($postPurchaseOrder->getOrderIncrementId());
        $this->assertSessionMessages(self::isEmpty(), MessageInterface::TYPE_ERROR);
        $successMessage = 'Successfully placed order #test_order_1 from purchase order #'
            . $postPurchaseOrder->getIncrementId()
            . '.';
        $this->assertSessionMessages(
            $this->equalTo([(string)__($successMessage)]),
            MessageInterface::TYPE_SUCCESS
        );

        $order = $this->objectManager->get(OrderRepositoryInterface::class)->get($postPurchaseOrder->getOrderId());
        self::assertEquals($order->getIncrementId(), $postPurchaseOrder->getOrderIncrementId());
        self::assertEquals($order->getGrandTotal(), $purchaseOrder->getSnapshotQuote()->getGrandTotal());
        $this->session->logout();

        // Assert email notification
        /** @var TransportBuilderMock $transportBuilderMock */
        $transportBuilderMock = $this->objectManager->get(TransportBuilderMock::class);
        $sentMessage = $transportBuilderMock->getSentMessage();
        self::assertStringContainsString('order confirmation', $sentMessage->getSubject());
        self::assertStringContainsString(
            'Thank you for your order from ',
            $sentMessage->getBody()->getParts()[0]->getRawContent()
        );
        self::assertStringContainsString(
            "Your Order <span class=\"no-link\">#test_order_1</span>",
            $sentMessage->getBody()->getParts()[0]->getRawContent()
        );
    }

    /**
     * Verify a purchase place order totals with changed cart price rule rate with coupon
     *
     * @param string $status
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     * @throws \Magento\Framework\Exception\InputException
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @dataProvider convertablePurchaseOrderStatusDataProvider
     * @magentoDataFixture Magento/PurchaseOrder/_files/purchase_orders_with_coupon_applied.php
     */
    public function testPlaceOrderActionAsCompanyAdminApprovedPurchaseOrderWithCartPriceRuleCouponChange($status)
    {
        $purchaseOrder = $this->getPurchaseOrder('admin@magento.com', 'customer@example.com', $status);
        $id = $purchaseOrder->getEntityId();
        //change applied rule discount rate
        $ruleRepository = $this->objectManager->get(RuleRepositoryInterface::class);
        $appliedRule = $ruleRepository->getById($purchaseOrder->getSnapshotQuote()->getAppliedRuleIds());
        $appliedRule->setDiscountAmount(1);
        $ruleRepository->save($appliedRule);
        $this->dispatch(self::URI . '/request_id/' . $id);
        // assert result
        $postPurchaseOrder = $this->objectManager->get(PurchaseOrderRepositoryInterface::class)->getById($id);
        self::assertEquals(PurchaseOrderInterface::STATUS_ORDER_PLACED, $postPurchaseOrder->getStatus());
        self::assertNotNull($postPurchaseOrder->getOrderId());
        self::assertNotNull($postPurchaseOrder->getOrderIncrementId());
        $this->assertSessionMessages(self::isEmpty(), MessageInterface::TYPE_ERROR);
        $successMessage = 'Successfully placed order #test_order_1 from purchase order #'
            . $postPurchaseOrder->getIncrementId()
            . '.';
        $this->assertSessionMessages(
            $this->equalTo([(string)__($successMessage)]),
            MessageInterface::TYPE_SUCCESS
        );

        $order = $this->objectManager->get(OrderRepositoryInterface::class)->get($postPurchaseOrder->getOrderId());
        self::assertEquals($order->getIncrementId(), $postPurchaseOrder->getOrderIncrementId());
        self::assertEquals($order->getGrandTotal(), $purchaseOrder->getSnapshotQuote()->getGrandTotal());
        $this->session->logout();

        // Assert email notification
        /** @var TransportBuilderMock $transportBuilderMock */
        $transportBuilderMock = $this->objectManager->get(TransportBuilderMock::class);
        $sentMessage = $transportBuilderMock->getSentMessage();
        self::assertStringContainsString('order confirmation', $sentMessage->getSubject());
        self::assertStringContainsString(
            'Thank you for your order from ',
            $sentMessage->getBody()->getParts()[0]->getRawContent()
        );
        self::assertStringContainsString(
            "Your Order <span class=\"no-link\">#test_order_1</span>",
            $sentMessage->getBody()->getParts()[0]->getRawContent()
        );
    }

    /**
     * Verify a purchase place order totals with changed cart price rule rate without coupon
     *
     * @param string $status
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     * @throws \Magento\Framework\Exception\InputException
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @dataProvider convertablePurchaseOrderStatusDataProvider
     * @magentoDataFixture Magento/PurchaseOrder/_files/purchase_orders_with_salesrule_without_coupon_applied.php
     */
    public function testPlaceOrderActionAsCompanyAdminApprovedPurchaseOrderWithCartPriceRuleNoCouponChange($status)
    {
        $purchaseOrder = $this->getPurchaseOrder('admin@magento.com', 'customer@example.com', $status);
        $id = $purchaseOrder->getEntityId();
        //change applied rule discount rate
        $ruleRepository = $this->objectManager->get(RuleRepositoryInterface::class);
        $appliedRule = $ruleRepository->getById($purchaseOrder->getSnapshotQuote()->getAppliedRuleIds());
        $appliedRule->setDiscountAmount(1);
        $ruleRepository->save($appliedRule);
        $this->dispatch(self::URI . '/request_id/' . $id);
        // assert result
        $postPurchaseOrder = $this->objectManager->get(PurchaseOrderRepositoryInterface::class)->getById($id);
        self::assertEquals(PurchaseOrderInterface::STATUS_ORDER_PLACED, $postPurchaseOrder->getStatus());
        self::assertNotNull($postPurchaseOrder->getOrderId());
        self::assertNotNull($postPurchaseOrder->getOrderIncrementId());
        $this->assertSessionMessages(self::isEmpty(), MessageInterface::TYPE_ERROR);
        $successMessage = 'Successfully placed order #test_order_1 from purchase order #'
            . $postPurchaseOrder->getIncrementId()
            . '.';
        $this->assertSessionMessages(
            self::equalTo([(string)__($successMessage)]),
            MessageInterface::TYPE_SUCCESS
        );

        $order = $this->objectManager->get(OrderRepositoryInterface::class)->get($postPurchaseOrder->getOrderId());
        self::assertEquals($order->getIncrementId(), $postPurchaseOrder->getOrderIncrementId());
        self::assertEquals($order->getGrandTotal(), $purchaseOrder->getSnapshotQuote()->getGrandTotal());
        $this->session->logout();

        // Assert email notification
        /** @var TransportBuilderMock $transportBuilderMock */
        $transportBuilderMock = $this->objectManager->get(TransportBuilderMock::class);
        $sentMessage = $transportBuilderMock->getSentMessage();
        self::assertStringContainsString('order confirmation', $sentMessage->getSubject());
        self::assertStringContainsString(
            'Thank you for your order from ',
            $sentMessage->getBody()->getParts()[0]->getRawContent()
        );
        self::assertStringContainsString(
            "Your Order <span class=\"no-link\">#test_order_1</span>",
            $sentMessage->getBody()->getParts()[0]->getRawContent()
        );
    }

    /**
     * Verify a purchase place order totals with disabled cart price rule without coupon
     *
     * @param string $status
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     * @throws \Magento\Framework\Exception\InputException
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @dataProvider convertablePurchaseOrderStatusDataProvider
     * @magentoDataFixture Magento/PurchaseOrder/_files/purchase_orders_with_salesrule_without_coupon_applied.php
     */
    public function testPlaceOrderActionAsCompanyAdminApprovedPurchaseOrderWithCartPriceRuleNoCouponDisable($status)
    {
        $purchaseOrder = $this->getPurchaseOrder('admin@magento.com', 'customer@example.com', $status);
        $id = $purchaseOrder->getEntityId();
        //change applied rule discount rate
        $ruleRepository = $this->objectManager->get(RuleRepositoryInterface::class);
        $appliedRule = $ruleRepository->getById($purchaseOrder->getSnapshotQuote()->getAppliedRuleIds());
        $appliedRule->setIsActive(false);
        $ruleRepository->save($appliedRule);
        $this->dispatch(self::URI . '/request_id/' . $id);
        // assert result
        $postPurchaseOrder = $this->objectManager->get(PurchaseOrderRepositoryInterface::class)->getById($id);
        self::assertEquals(PurchaseOrderInterface::STATUS_ORDER_PLACED, $postPurchaseOrder->getStatus());
        self::assertNotNull($postPurchaseOrder->getOrderId());
        self::assertNotNull($postPurchaseOrder->getOrderIncrementId());
        $this->assertSessionMessages(self::isEmpty(), MessageInterface::TYPE_ERROR);
        $successMessage = 'Successfully placed order #test_order_1 from purchase order #'
            . $postPurchaseOrder->getIncrementId()
            . '.';
        $this->assertSessionMessages(
            self::equalTo([(string)__($successMessage)]),
            MessageInterface::TYPE_SUCCESS
        );

        $order = $this->objectManager->get(OrderRepositoryInterface::class)->get($postPurchaseOrder->getOrderId());
        self::assertEquals($order->getIncrementId(), $postPurchaseOrder->getOrderIncrementId());
        self::assertEquals($order->getGrandTotal(), $purchaseOrder->getSnapshotQuote()->getGrandTotal());
        $this->session->logout();

        // Assert email notification
        /** @var TransportBuilderMock $transportBuilderMock */
        $transportBuilderMock = $this->objectManager->get(TransportBuilderMock::class);
        $sentMessage = $transportBuilderMock->getSentMessage();
        self::assertStringContainsString('order confirmation', $sentMessage->getSubject());
        self::assertStringContainsString(
            'Thank you for your order from ',
            $sentMessage->getBody()->getParts()[0]->getRawContent()
        );
        self::assertStringContainsString(
            "Your Order <span class=\"no-link\">#test_order_1</span>",
            $sentMessage->getBody()->getParts()[0]->getRawContent()
        );
    }

    /**
     * Verify a purchase place order totals with disabled cart price rule with coupon code
     *
     * @param string $status
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     * @throws \Magento\Framework\Exception\InputException
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @dataProvider convertablePurchaseOrderStatusDataProvider
     * @magentoDataFixture Magento/PurchaseOrder/_files/purchase_orders_with_coupon_applied.php
     */
    public function testPlaceOrderActionAsCompanyAdminApprovedPOWithCartPriceRuleCouponDiscountAfterRuleDisable($status)
    {
        $purchaseOrder = $this->getPurchaseOrder('admin@magento.com', 'customer@example.com', $status);
        $id = $purchaseOrder->getEntityId();
        //change applied rule discount rate
        $ruleRepository = $this->objectManager->get(RuleRepositoryInterface::class);
        $appliedRule = $ruleRepository->getById($purchaseOrder->getSnapshotQuote()->getAppliedRuleIds());
        $appliedRule->setIsActive(false);
        $ruleRepository->save($appliedRule);
        $this->dispatch(self::URI . '/request_id/' . $id);
        // assert result
        $postPurchaseOrder = $this->objectManager->get(PurchaseOrderRepositoryInterface::class)->getById($id);
        self::assertEquals(PurchaseOrderInterface::STATUS_ORDER_PLACED, $postPurchaseOrder->getStatus());
        self::assertNotNull($postPurchaseOrder->getOrderId());
        self::assertNotNull($postPurchaseOrder->getOrderIncrementId());
        $this->assertSessionMessages(self::isEmpty(), MessageInterface::TYPE_ERROR);
        $successMessage = 'Successfully placed order #test_order_1 from purchase order #'
            . $postPurchaseOrder->getIncrementId()
            . '.';
        $this->assertSessionMessages(
            self::equalTo([(string)__($successMessage)]),
            MessageInterface::TYPE_SUCCESS
        );

        $order = $this->objectManager->get(OrderRepositoryInterface::class)->get($postPurchaseOrder->getOrderId());
        self::assertEquals($order->getIncrementId(), $postPurchaseOrder->getOrderIncrementId());
        self::assertEquals($order->getGrandTotal(), $purchaseOrder->getSnapshotQuote()->getGrandTotal());
        $this->session->logout();

        // Assert email notification
        /** @var TransportBuilderMock $transportBuilderMock */
        $transportBuilderMock = $this->objectManager->get(TransportBuilderMock::class);
        $sentMessage = $transportBuilderMock->getSentMessage();
        self::assertStringContainsString('order confirmation', $sentMessage->getSubject());
        self::assertStringContainsString(
            'Thank you for your order from ',
            $sentMessage->getBody()->getParts()[0]->getRawContent()
        );
        self::assertStringContainsString(
            "Your Order <span class=\"no-link\">#test_order_1</span>",
            $sentMessage->getBody()->getParts()[0]->getRawContent()
        );
    }

    /**
     * Verify a purchase place order totals with applied catalog rule
     *
     * @param string $status
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     * @throws \Magento\Framework\Exception\InputException
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @magentoDataFixture Magento/PurchaseOrder/_files/purchase_orders_with_catalogrule.php
     * @dataProvider convertablePurchaseOrderStatusDataProvider
     *
     * @magentoAppArea frontend
     * @magentoDbIsolation disabled
     */
    public function testPlaceOrderActionAsCompanyAdminApprovedPurchaseOrderWithCatalogPriceRule($status)
    {
        $purchaseOrder = $this->getPurchaseOrder('admin@magento.com', 'customer@example.com', $status);
        $id = $purchaseOrder->getEntityId();
        $this->dispatch(self::URI . '/request_id/' . $id);
        // assert result
        $postPurchaseOrder = $this->objectManager->get(PurchaseOrderRepositoryInterface::class)->getById($id);
        self::assertEquals(PurchaseOrderInterface::STATUS_ORDER_PLACED, $postPurchaseOrder->getStatus());
        self::assertNotNull($postPurchaseOrder->getOrderId());
        self::assertNotNull($postPurchaseOrder->getOrderIncrementId());
        $this->assertSessionMessages(self::isEmpty(), MessageInterface::TYPE_ERROR);
        $successMessage = 'Successfully placed order #test_order_with_virtual_product from purchase order #'
            . $postPurchaseOrder->getIncrementId()
            . '.';
        $this->assertSessionMessages(
            self::equalTo([(string)__($successMessage)]),
            MessageInterface::TYPE_SUCCESS
        );

        $order = $this->objectManager->get(OrderRepositoryInterface::class)->get($postPurchaseOrder->getOrderId());
        self::assertEquals($order->getIncrementId(), $postPurchaseOrder->getOrderIncrementId());
        self::assertEquals($order->getGrandTotal(), $purchaseOrder->getSnapshotQuote()->getGrandTotal());
        $this->session->logout();

        // Assert email notification
        /** @var TransportBuilderMock $transportBuilderMock */
        $transportBuilderMock = $this->objectManager->get(TransportBuilderMock::class);
        $sentMessage = $transportBuilderMock->getSentMessage();
        self::assertStringContainsString('order confirmation', $sentMessage->getSubject());
        self::assertStringContainsString(
            'Thank you for your order from ',
            $sentMessage->getBody()->getParts()[0]->getRawContent()
        );
        self::assertStringContainsString(
            "Your Order <span class=\"no-link\">#test_order_with_virtual_product</span>",
            $sentMessage->getBody()->getParts()[0]->getRawContent()
        );
    }

    /**
     * Verify a purchase place order totals with removed catalog rule
     *
     * @param string $status
     * @throws \Magento\Framework\Exception\CouldNotDeleteException
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     * @throws \Magento\Framework\Exception\InputException
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @magentoDataFixture Magento/PurchaseOrder/_files/purchase_orders_with_catalogrule.php
     * @dataProvider convertablePurchaseOrderStatusDataProvider
     * @magentoAppArea frontend
     * @magentoDbIsolation disabled
     */
    public function testPlaceOrderActionAsCompanyAdminApprovedPurchaseOrderWithCatalogPriceRuleRemove($status)
    {
        $purchaseOrder = $this->getPurchaseOrder('admin@magento.com', 'customer@example.com', $status);
        $id = $purchaseOrder->getEntityId();
        //remove catalog rule
        $ruleCollection = $this->catalogRuleCollectionFactory->create();
        $ruleCollection->addFieldToFilter('name', ['eq' => 'Test Catalog Rule for logged user']);
        $ruleCollection->setPageSize(1);
        /** @var RuleInterface $catalogRule */
        $catalogRule = $ruleCollection->getFirstItem();
        $this->objectManager->get(CatalogRuleRepositoryInterface::class)->delete($catalogRule);
        $this->catalogRuleIndexBuilder->reindexFull();
        $this->dispatch(self::URI . '/request_id/' . $id);
        // assert result
        $postPurchaseOrder = $this->objectManager->get(PurchaseOrderRepositoryInterface::class)->getById($id);
        self::assertEquals(PurchaseOrderInterface::STATUS_ORDER_PLACED, $postPurchaseOrder->getStatus());
        self::assertNotNull($postPurchaseOrder->getOrderId());
        self::assertNotNull($postPurchaseOrder->getOrderIncrementId());
        $this->assertSessionMessages(self::isEmpty(), MessageInterface::TYPE_ERROR);
        $successMessage = 'Successfully placed order #test_order_with_virtual_product from purchase order #'
            . $postPurchaseOrder->getIncrementId()
            . '.';
        $this->assertSessionMessages(
            self::equalTo([(string)__($successMessage)]),
            MessageInterface::TYPE_SUCCESS
        );

        $order = $this->objectManager->get(OrderRepositoryInterface::class)->get($postPurchaseOrder->getOrderId());
        self::assertEquals($order->getIncrementId(), $postPurchaseOrder->getOrderIncrementId());
        self::assertEquals($order->getGrandTotal(), $purchaseOrder->getSnapshotQuote()->getGrandTotal());
        $this->session->logout();

        // Assert email notification
        /** @var TransportBuilderMock $transportBuilderMock */
        $transportBuilderMock = $this->objectManager->get(TransportBuilderMock::class);
        $sentMessage = $transportBuilderMock->getSentMessage();
        self::assertStringContainsString('order confirmation', $sentMessage->getSubject());
        self::assertStringContainsString(
            'Thank you for your order from ',
            $sentMessage->getBody()->getParts()[0]->getRawContent()
        );
        self::assertStringContainsString(
            "Your Order <span class=\"no-link\">#test_order_with_virtual_product</span>",
            $sentMessage->getBody()->getParts()[0]->getRawContent()
        );
    }

    /**
     * Verify a purchase place order totals with disabled catalog price rule
     *
     * @param string $status
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     * @throws \Magento\Framework\Exception\InputException
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @magentoDataFixture Magento/PurchaseOrder/_files/purchase_orders_with_catalogrule.php
     * @dataProvider convertablePurchaseOrderStatusDataProvider
     * @magentoAppArea frontend
     * @magentoDbIsolation disabled
     */
    public function testPlaceOrderActionAsCompanyAdminApprovedPurchaseOrderWithCatalogPriceRuleDisable($status)
    {
        $purchaseOrder = $this->getPurchaseOrder('admin@magento.com', 'customer@example.com', $status);
        $id = $purchaseOrder->getEntityId();
        //remove catalog rule
        $ruleCollection = $this->catalogRuleCollectionFactory->create();
        $ruleCollection->addFieldToFilter('name', ['eq' => 'Test Catalog Rule for logged user']);
        $ruleCollection->setPageSize(1);
        /** @var RuleInterface $catalogRule */
        $catalogRule = $ruleCollection->getFirstItem();
        $catalogRule->setIsActive(false);
        $this->objectManager->get(CatalogRuleRepositoryInterface::class)->save($catalogRule);
        $this->catalogRuleIndexBuilder->reindexFull();
        $this->dispatch(self::URI . '/request_id/' . $id);
        // assert result
        $postPurchaseOrder = $this->objectManager->get(PurchaseOrderRepositoryInterface::class)->getById($id);
        self::assertEquals(PurchaseOrderInterface::STATUS_ORDER_PLACED, $postPurchaseOrder->getStatus());
        self::assertNotNull($postPurchaseOrder->getOrderId());
        self::assertNotNull($postPurchaseOrder->getOrderIncrementId());
        $this->assertSessionMessages(self::isEmpty(), MessageInterface::TYPE_ERROR);
        $successMessage = 'Successfully placed order #test_order_with_virtual_product from purchase order #'
            . $postPurchaseOrder->getIncrementId()
            . '.';
        $this->assertSessionMessages(
            self::equalTo([(string)__($successMessage)]),
            MessageInterface::TYPE_SUCCESS
        );

        $order = $this->objectManager->get(OrderRepositoryInterface::class)->get($postPurchaseOrder->getOrderId());
        self::assertEquals($order->getIncrementId(), $postPurchaseOrder->getOrderIncrementId());
        self::assertEquals($order->getGrandTotal(), $purchaseOrder->getSnapshotQuote()->getGrandTotal());
        $this->session->logout();

        // Assert email notification
        /** @var TransportBuilderMock $transportBuilderMock */
        $transportBuilderMock = $this->objectManager->get(TransportBuilderMock::class);
        $sentMessage = $transportBuilderMock->getSentMessage();
        self::assertStringContainsString('order confirmation', $sentMessage->getSubject());
        self::assertStringContainsString(
            'Thank you for your order from ',
            $sentMessage->getBody()->getParts()[0]->getRawContent()
        );
        self::assertStringContainsString(
            "Your Order <span class=\"no-link\">#test_order_with_virtual_product</span>",
            $sentMessage->getBody()->getParts()[0]->getRawContent()
        );
    }

    /**
     * Verify a purchase place order totals with catalog price rule changed rate
     *
     * @param string $status
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     * @throws \Magento\Framework\Exception\InputException
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @magentoDataFixture Magento/PurchaseOrder/_files/purchase_orders_with_catalogrule.php
     * @dataProvider convertablePurchaseOrderStatusDataProvider
     * @magentoAppArea frontend
     * @magentoDbIsolation disabled
     */
    public function testPlaceOrderActionAsCompanyAdminApprovedPurchaseOrderWithCatalogPriceRuleChangeRate($status)
    {
        $purchaseOrder = $this->getPurchaseOrder('admin@magento.com', 'customer@example.com', $status);
        $id = $purchaseOrder->getEntityId();
        //remove catalog rule
        $ruleCollection = $this->catalogRuleCollectionFactory->create();
        $ruleCollection->addFieldToFilter('name', ['eq' => 'Test Catalog Rule for logged user']);
        $ruleCollection->setPageSize(1);
        /** @var RuleInterface $catalogRule */
        $catalogRule = $ruleCollection->getFirstItem();
        $catalogRule->setDiscountAmount(1);
        $this->objectManager->get(CatalogRuleRepositoryInterface::class)->save($catalogRule);
        $this->catalogRuleIndexBuilder->reindexFull();
        $this->dispatch(self::URI . '/request_id/' . $id);
        // assert result
        $postPurchaseOrder = $this->objectManager->get(PurchaseOrderRepositoryInterface::class)->getById($id);
        self::assertEquals(PurchaseOrderInterface::STATUS_ORDER_PLACED, $postPurchaseOrder->getStatus());
        self::assertNotNull($postPurchaseOrder->getOrderId());
        self::assertNotNull($postPurchaseOrder->getOrderIncrementId());
        $this->assertSessionMessages(self::isEmpty(), MessageInterface::TYPE_ERROR);
        $successMessage = 'Successfully placed order #test_order_with_virtual_product from purchase order #'
            . $postPurchaseOrder->getIncrementId()
            . '.';
        $this->assertSessionMessages(
            $this->equalTo([(string)__($successMessage)]),
            MessageInterface::TYPE_SUCCESS
        );

        $order = $this->objectManager->get(OrderRepositoryInterface::class)->get($postPurchaseOrder->getOrderId());
        self::assertEquals($order->getIncrementId(), $postPurchaseOrder->getOrderIncrementId());
        self::assertEquals($order->getGrandTotal(), $purchaseOrder->getSnapshotQuote()->getGrandTotal());
        $this->session->logout();

        // Assert email notification
        /** @var TransportBuilderMock $transportBuilderMock */
        $transportBuilderMock = $this->objectManager->get(TransportBuilderMock::class);
        $sentMessage = $transportBuilderMock->getSentMessage();
        self::assertStringContainsString('order confirmation', $sentMessage->getSubject());
        self::assertStringContainsString(
            'Thank you for your order from ',
            $sentMessage->getBody()->getParts()[0]->getRawContent()
        );
        self::assertStringContainsString(
            "Your Order <span class=\"no-link\">#test_order_with_virtual_product</span>",
            $sentMessage->getBody()->getParts()[0]->getRawContent()
        );
    }
}
