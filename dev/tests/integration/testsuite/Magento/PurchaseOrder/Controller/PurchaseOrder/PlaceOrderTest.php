<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\PurchaseOrder\Controller\PurchaseOrder;

use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Model\Session;
use Magento\Framework\App\Request\Http;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\PurchaseOrder\Api\Data\PurchaseOrderInterface;
use Magento\PurchaseOrder\Api\PurchaseOrderRepositoryInterface;
use Magento\PurchaseOrder\Model\Comment;
use Magento\PurchaseOrder\Model\CommentManagement;
use Magento\Framework\Message\MessageInterface;
use Magento\TestFramework\Helper\Bootstrap;

/**
 * Controller test class for the purchase order place order.
 *
 * @magentoAppArea frontend
 * @magentoAppIsolation enabled
 * @see \Magento\PurchaseOrder\Controller\PurchaseOrder\PlaceOrder
 */
class PlaceOrderTest extends PurchaseOrderAbstract
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
     * @var CommentManagement
     */
    private $commentManagement;

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
        $this->commentManagement = $this->objectManager->get(CommentManagement::class);
    }

    /**
     * @magentoDataFixture Magento/PurchaseOrder/_files/company_with_structure_and_purchase_orders.php
     */
    public function testPlaceOrderActionGetRequest()
    {
        $purchaseOrder = $this->getPurchaseOrderForCustomer('alex.smith@example.com');
        $this->dispatch(self::URI . '/request_id/' . $purchaseOrder->getEntityId());
        $this->assert404NotFound();
    }

    /**
     * @magentoDataFixture Magento/PurchaseOrder/_files/company_with_structure_and_purchase_orders.php
     */
    public function testPlaceOrderActionAsGuestUser()
    {
        $this->getRequest()->setMethod(Http::METHOD_POST);
        $purchaseOrder = $this->getPurchaseOrderForCustomer('alex.smith@example.com');
        $this->dispatch(self::URI . '/request_id/' . $purchaseOrder->getEntityId());
        self::assertEquals(302, $this->getResponse()->getHttpResponseCode());
        $this->assertRedirect(self::stringContains('customer/account/login'));
    }

    /**
     * @magentoDataFixture Magento/PurchaseOrder/_files/company_with_structure_and_purchase_orders.php
     * @magentoDataFixture Magento/Customer/_files/customer.php
     */
    public function testPlaceOrderActionAsNonCompanyUser()
    {
        $nonCompanyUser = $this->objectManager->get(CustomerRepositoryInterface::class)->get('customer@example.com');
        $this->session->loginById($nonCompanyUser->getId());

        $this->getRequest()->setMethod(Http::METHOD_POST);
        $purchaseOrder = $this->getPurchaseOrderForCustomer('alex.smith@example.com');
        $this->dispatch(self::URI . '/request_id/' . $purchaseOrder->getEntityId());
        self::assertEquals(302, $this->getResponse()->getHttpResponseCode());
        $this->assertRedirect(self::stringContains('noroute'));

        $this->session->logout();
    }

    /**
     * @param string $currentUserEmail
     * @param string $createdByUserEmail
     * @param int $expectedHttpResponseCode
     * @param string $expectedRedirect
     * @dataProvider placeOrderActionAsCompanyUserDataProvider
     * @magentoDataFixture Magento/PurchaseOrder/_files/company_with_structure_and_purchase_orders.php
     */
    public function testPlaceOrderActionAsCompanyUser(
        $currentUserEmail,
        $createdByUserEmail,
        $expectedHttpResponseCode,
        $expectedRedirect
    ) {
        // Log in as the current user
        $currentUser = $this->objectManager->get(CustomerRepositoryInterface::class)->get($currentUserEmail);
        $this->session->loginById($currentUser->getId());

        // Dispatch the request
        $this->getRequest()->setMethod(Http::METHOD_POST);
        $purchaseOrder = $this->getPurchaseOrderForCustomer($createdByUserEmail);
        $this->dispatch(self::URI . '/request_id/' . $purchaseOrder->getEntityId());

        // Perform assertions
        self::assertEquals($expectedHttpResponseCode, $this->getResponse()->getHttpResponseCode());
        $this->assertRedirect(self::stringContains($expectedRedirect));

        $this->session->logout();
    }

    /**
     * @param string $currentUserEmail
     * @param string $createdByUserEmail
     * @param $usedPaymentMethod
     * @param int $expectedHttpResponseCode
     * @param string $expectedRedirect
     *
     * @throws LocalizedException
     * @throws NoSuchEntityException
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     * @throws \Magento\Framework\Exception\InputException
     * @dataProvider placeOrderActionAsCompanyUserWithOnlinePaymentDataProvider
     * @magentoDataFixture Magento/PurchaseOrder/_files/company_with_structure_and_purchase_orders.php
     */
    public function testPlaceOrderActionAsCompanyUserWithOnlinePaymentUsed(
        $currentUserEmail,
        $createdByUserEmail,
        $usedPaymentMethod,
        $expectedHttpResponseCode,
        $expectedRedirect
    ) {
        // Log in as the current user
        $currentUser = $this->objectManager->get(CustomerRepositoryInterface::class)->get($currentUserEmail);
        $this->session->loginById($currentUser->getId());

        // Dispatch the request
        $this->getRequest()->setMethod(Http::METHOD_POST)->setParam('payment_redirect', '1');
        $purchaseOrder = $this->getPurchaseOrderForCustomer($createdByUserEmail);
        $purchaseOrder->setPaymentMethod($usedPaymentMethod);
        $purchaseOrder->setStatus(PurchaseOrderInterface::STATUS_APPROVED_PENDING_PAYMENT);
        $this->objectManager->get(PurchaseOrderRepositoryInterface::class)->save($purchaseOrder);
        $this->dispatch(self::URI . '/request_id/' . $purchaseOrder->getEntityId());

        // Perform assertions
        self::assertEquals($expectedHttpResponseCode, $this->getResponse()->getHttpResponseCode());
        $this->assertRedirect(self::stringContains($expectedRedirect));

        $this->session->logout();
    }

    /**
     * Data provider for a place order action scenario when online payment is selected for company users.
     *
     * @return array
     */
    public function placeOrderActionAsCompanyUserWithOnlinePaymentDataProvider()
    {
        return [
            'place_order_my_purchase_order' => [
                'current_customer' => 'veronica.costello@example.com',
                'created_by_customer' => 'veronica.costello@example.com',
                'usedPaymentMethod' => 'paypal_express',
                'expected_http_response_code' => 302,
                'expected_redirect' => 'checkout/index/index/purchaseOrderId/'
            ]
        ];
    }

    /**
     * Data provider for various place order action scenarios for company users.
     *
     * @return array
     */
    public function placeOrderActionAsCompanyUserDataProvider()
    {
        return [
            'place_order_my_purchase_order' => [
                'current_customer' => 'veronica.costello@example.com',
                'created_by_customer' => 'veronica.costello@example.com',
                'expected_http_response_code' => 302,
                'expected_redirect' => 'company/accessdenied'
            ],
            'place_order_subordinate_purchase_order' => [
                'current_customer' => 'veronica.costello@example.com',
                'created_by_customer' => 'alex.smith@example.com',
                'expected_http_response_code' => 302,
                'expected_redirect' => 'company/accessdenied'
            ],
            'place_order_superior_purchase_order' => [
                'current_customer' => 'veronica.costello@example.com',
                'created_by_customer' => 'john.doe@example.com',
                'expected_http_response_code' => 302,
                'expected_redirect' => 'company/accessdenied'
            ]
        ];
    }

    /**
     * @magentoDataFixture Magento/PurchaseOrder/_files/company_with_structure_and_purchase_orders.php
     * @magentoDataFixture Magento/Company/_files/company_with_admin.php
     */
    public function testPlaceOrderActionAsOtherCompanyAdmin()
    {
        $otherCompanyAdmin = $this->objectManager->get(CustomerRepositoryInterface::class)->get('company-admin@example.com');
        $this->session->loginById($otherCompanyAdmin->getId());

        $this->getRequest()->setMethod(Http::METHOD_POST);
        $purchaseOrder = $this->getPurchaseOrderForCustomer('alex.smith@example.com');
        $this->dispatch(self::URI . '/request_id/' . $purchaseOrder->getEntityId());

        // Perform assertions
        self::assertEquals(302, $this->getResponse()->getHttpResponseCode());
        $this->assertRedirect(self::stringContains('company/accessdenied'));

        $this->session->logout();
    }

    /**
     * @magentoDataFixture Magento/PurchaseOrder/_files/purchase_orders.php
     */
    public function testPlaceOrderActionNonexistingPurchaseOrder()
    {
        $companyAdmin = $this->objectManager->get(CustomerRepositoryInterface::class)->get('admin@magento.com');
        $this->session->loginById($companyAdmin->getId());

        $this->getRequest()->setMethod(Http::METHOD_POST);
        $this->dispatch(self::URI . '/request_id/' . '5000');
        $this->assertRedirect(self::stringContains('company/accessdenied'));

        $this->session->logout();
    }

    /**
     * @magentoDataFixture Magento/PurchaseOrder/_files/purchase_orders.php
     * @dataProvider unconvertablePurchaseOrderStatusDataProvider
     * @param string $status
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     * @throws \Magento\Framework\Exception\InputException
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function testPlaceOrderAsCompanyAdminNonConvertablePurchaseOrder($status)
    {
        $purchaseOrder = $this->getPurchaseOrder('admin@magento.com', 'customer@example.com', $status);

        $this->dispatch(self::URI . '/request_id/' . $purchaseOrder->getEntityId());
        $message = 'Order cannot be placed with purchase order #' . $purchaseOrder->getIncrementId() . '.';
        $this->assertSessionMessages(self::equalTo([(string)__($message)]), MessageInterface::TYPE_ERROR);
        $this->session->logout();
    }

    /**
     * Data provider of purchase order statuses that do not allow approval.
     *
     * @return array[]
     */
    public function unconvertablePurchaseOrderStatusDataProvider()
    {
        return [
            [PurchaseOrderInterface::STATUS_PENDING],
            [PurchaseOrderInterface::STATUS_APPROVAL_REQUIRED],
            [PurchaseOrderInterface::STATUS_CANCELED],
            [PurchaseOrderInterface::STATUS_REJECTED],
            [PurchaseOrderInterface::STATUS_ORDER_PLACED],
            [PurchaseOrderInterface::STATUS_ORDER_IN_PROGRESS],
            [PurchaseOrderInterface::STATUS_APPROVED_PENDING_PAYMENT],
        ];
    }

    /**
     * Data provider of purchase order statuses that allow approval.
     *
     * @return array[]
     */
    public function convertablePurchaseOrderStatusDataProvider()
    {
        return [
            'Approved' => [PurchaseOrderInterface::STATUS_APPROVED],
            'Approved - Order Failed' => [PurchaseOrderInterface::STATUS_ORDER_FAILED]
        ];
    }

    /**
     * Verify a company admin can place order with a comment
     *
     * @param string $status
     * @throws LocalizedException
     * @throws NoSuchEntityException
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     * @throws \Magento\Framework\Exception\InputException
     * @dataProvider convertablePurchaseOrderStatusDataProvider
     * @magentoDataFixture Magento/PurchaseOrder/_files/purchase_orders.php
     */
    public function testPlaceOrderActionAsCompanyAdminWithCommentPurchaseOrder($status)
    {
        $companyAdmin = $this->objectManager->get(CustomerRepositoryInterface::class)->get('admin@magento.com');
        $this->session->loginById($companyAdmin->getId());

        $purchaserEmail = 'customer@example.com';
        $purchaseOrder = $this->getPurchaseOrderForCustomer($purchaserEmail);
        $purchaseOrder->setStatus($status);
        $this->objectManager->get(PurchaseOrderRepositoryInterface::class)->save($purchaseOrder);

        // Place the order against the Purchase Order
        $this->getRequest()->setMethod(Http::METHOD_POST);
        $this->getRequest()->setParams([
            'comment' => 'This is our test comment'
        ]);
        $this->dispatch(self::URI . '/request_id/' . $purchaseOrder->getEntityId());

        // Assert the Purchase Order is now approved
        $postPurchaseOrder = $this->objectManager->get(PurchaseOrderRepositoryInterface::class)->getById($purchaseOrder->getEntityId());
        self::assertEquals(PurchaseOrderInterface::STATUS_ORDER_PLACED, $postPurchaseOrder->getStatus());

        // Verify the comment was added to the Purchase Order
        $comments = $this->commentManagement->getPurchaseOrderComments($purchaseOrder->getEntityId());
        self::assertEquals(1, $comments->getSize());
        /** @var Comment $comment */
        $comment = $comments->getFirstItem();
        self::assertEquals('This is our test comment', $comment->getComment());
        self::assertEquals($companyAdmin->getId(), $comment->getCreatorId());

        $this->session->logout();
    }
}
