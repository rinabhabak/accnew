<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\PurchaseOrder\Controller\PurchaseOrder;

use Magento\Company\Api\CompanyRepositoryInterface;
use Magento\Company\Api\Data\CompanyInterface;
use Magento\Company\Api\RoleRepositoryInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Model\CustomerRegistry;
use Magento\Customer\Model\Session;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\App\Config\MutableScopeConfigInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\PurchaseOrder\Api\Data\PurchaseOrderInterface;
use Magento\PurchaseOrder\Api\PurchaseOrderRepositoryInterface;
use Magento\PurchaseOrder\Model\Company\Config\RepositoryInterface as CompanyConfigRepositoryInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\ObjectManager;
use Magento\TestFramework\TestCase\AbstractController;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\CatalogInventory\Model\Stock\Status as StockStatus;
use Magento\Catalog\Model\Product\Attribute\Source\Status as ProductStatus;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\CatalogInventory\Model\StockRegistryStorage;
use Magento\Quote\Model\ResourceModel\Quote\Item\Collection as QuoteItemCollection;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\StateException;

/**
 * Controller test class for the purchase order payment details page.
 *
 * @see \Magento\PurchaseOrder\Controller\PurchaseOrder\View
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.TooManyFields)
 * @magentoAppArea frontend
 * @magentoAppIsolation enabled
 */
class PaymentDetailsTest extends AbstractController
{
    const URI = 'checkout/index/index';

    /**
     * @var CompanyRepositoryInterface
     */
    private $companyRepository;

    /**
     * @var CompanyConfigRepositoryInterface
     */
    private $companyConfigRepository;

    /**
     * @var RoleRepositoryInterface
     */
    private $roleRepository;

    /**
     * @var CustomerRepositoryInterface
     */
    private $customerRepository;

    /**
     * @var PurchaseOrderRepositoryInterface
     */
    private $purchaseOrderRepository;

    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @var Session
     */
    private $session;

    /**
     * @var CustomerRegistry
     */
    private $customerRegistry;

    /**
     * @var CartRepositoryInterface
     */
    private $cartRepository;

    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @var StockRegistryStorage
     */
    private $stockRegistryStorage;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        parent::setUp();

        /** @var ObjectManager $objectManager */
        $objectManager = Bootstrap::getObjectManager();

        $this->companyRepository = $objectManager->get(CompanyRepositoryInterface::class);
        $this->companyConfigRepository = $objectManager->get(CompanyConfigRepositoryInterface::class);
        $this->roleRepository = $objectManager->get(RoleRepositoryInterface::class);
        $this->customerRepository = $objectManager->get(CustomerRepositoryInterface::class);
        $this->purchaseOrderRepository = $objectManager->get(PurchaseOrderRepositoryInterface::class);
        $this->searchCriteriaBuilder = $objectManager->get(SearchCriteriaBuilder::class);
        $this->session = $objectManager->get(Session::class);
        $this->customerRegistry = $objectManager->get(CustomerRegistry::class);
        $this->stockRegistryStorage = $objectManager->get(StockRegistryStorage::class);
        $this->cartRepository = $objectManager->get(CartRepositoryInterface::class);
        $this->productRepository = $objectManager->get(ProductRepositoryInterface::class);

        // Enable company functionality for the website scope
        $this->setWebsiteConfig('btob/website_configuration/company_active', true);

        // Enable purchase order functionality for the website scope
        $this->setWebsiteConfig('btob/website_configuration/purchaseorder_enabled', true);
    }

    /**
     * Enable/Disable configuration for the website scope.
     *
     * @param string $path
     * @param bool $isEnabled
     */
    private function setWebsiteConfig(string $path, bool $isEnabled)
    {
        /** @var MutableScopeConfigInterface $scopeConfig */
        $scopeConfig = Bootstrap::getObjectManager()->get(MutableScopeConfigInterface::class);
        $scopeConfig->setValue(
            $path,
            $isEnabled ? '1' : '0',
            ScopeInterface::SCOPE_WEBSITE
        );
    }

    /**
     * Enable/Disable purchase order functionality on a per company basis.
     *
     * @param string $companyName
     * @param bool $isEnabled
     * @throws LocalizedException
     */
    private function setCompanyPurchaseOrderConfig(string $companyName, bool $isEnabled)
    {
        $this->searchCriteriaBuilder->addFilter('company_name', $companyName);
        $searchCriteria = $this->searchCriteriaBuilder->create();
        $results = $this->companyRepository->getList($searchCriteria)->getItems();

        /** @var CompanyInterface $company */
        $company = reset($results);

        $companyConfig = $this->companyConfigRepository->get($company->getId());
        $companyConfig->setIsPurchaseOrderEnabled($isEnabled);

        $this->companyConfigRepository->save($companyConfig);
    }

    /**
     * Test payment details page. Checking access, po quote total
     *
     * @param string $customerEmail
     * @param string $purchaseOrderCreatorEmail
     * @param string $orderStatus
     * @param int $expectedHttpResponseCode
     * @param string $expectedRedirect
     * @throws LocalizedException
     * @dataProvider paymentDetailsPageDataProvider
     * @magentoDataFixture Magento/PurchaseOrder/_files/company_with_structure_and_purchase_orders.php
     */
    public function testPaymentDetailsPage(
        $customerEmail,
        $purchaseOrderCreatorEmail,
        $orderStatus,
        $expectedHttpResponseCode,
        $expectedRedirect
    ) {
        $this->setCompanyPurchaseOrderConfig('Magento', true);

        // Log in as the current user
        $purchaseOrderId = $this->getPurchaseOrderForCustomer($purchaseOrderCreatorEmail)->getEntityId();
        $currentUser = $this->customerRegistry->retrieveByEmail($customerEmail);

        $this->getRequest()->setParam('purchaseOrderId', $purchaseOrderId);
        $this->session->setCustomerAsLoggedIn($currentUser);

        $purchaseOrder = $this->purchaseOrderRepository->getById($purchaseOrderId);
        $purchaseOrder->setStatus($orderStatus);
        $this->purchaseOrderRepository->save($purchaseOrder);

        // Dispatch the request to the view payment details page for the desired purchase order
        $this->dispatch(self::URI . '/purchaseOrderId/' . $purchaseOrderId);

        // Perform assertions
        $this->assertEquals($expectedHttpResponseCode, $this->getResponse()->getHttpResponseCode());

        if ($expectedRedirect) {
            $this->assertRedirect($this->stringContains($expectedRedirect));
        } else {
            $this->assertStringContainsString(
                '"grand_total":"' . $purchaseOrder->getGrandTotal(). '"',
                $this->getResponse()->getBody()
            );
        }

        $this->session->logout();
    }

    /**
     * Check redirect on payment details page with quote has an error
     *
     * @param string $customerEmail
     * @param string $purchaseOrderCreatorEmail
     * @param string $orderStatus
     * @param string $productStockStatus
     * @param string $productStatus
     * @param int $productQty
     * @param int $productCartQty
     * @param int $expectedHttpResponseCode
     * @param string $expectedRedirect
     *
     * @throws LocalizedException
     * @throws CouldNotSaveException
     * @throws InputException
     * @throws NoSuchEntityException
     * @throws StateException
     *
     * @dataProvider paymentDetailsPageQuoteErrorDataProvider
     * @magentoDataFixture Magento/PurchaseOrder/_files/company_with_structure_and_purchase_orders.php
     */
    public function testPaymentDetailsPageQuoteErrorRedirect(
        $customerEmail,
        $purchaseOrderCreatorEmail,
        $orderStatus,
        $productStockStatus,
        $productStatus,
        $productQty,
        $productCartQty,
        $expectedHttpResponseCode,
        $expectedRedirect
    ) {
        $this->setCompanyPurchaseOrderConfig('Magento', true);

        // Log in as the current user
        $purchaseOrderId = $this->getPurchaseOrderForCustomer($purchaseOrderCreatorEmail)->getEntityId();
        $currentUser = $this->customerRegistry->retrieveByEmail($customerEmail);

        $this->getRequest()->setParam('purchaseOrderId', $purchaseOrderId);
        $this->session->setCustomerAsLoggedIn($currentUser);

        $purchaseOrder = $this->purchaseOrderRepository->getById($purchaseOrderId);
        $quote = $this->cartRepository->get($purchaseOrder->getQuoteId());
        $purchaseOrder->setStatus($orderStatus);
        $this->purchaseOrderRepository->save($purchaseOrder);

        //init quote product data
        $product = $this->productRepository->get(
            'virtual-product',
            false,
            0
        );

        $product->setStockData(
            [
                'qty' => $productQty,
                'is_in_stock' => $productStockStatus
            ]
        );
        $product->setStatus($productStatus);
        $this->productRepository->save($product);
        $this->stockRegistryStorage->clean();

        //init purchase order quote
        /** @var QuoteItemCollection $itemCollection */
        $itemCollection = $quote->getItemsCollection(false);
        $quoteItem = $itemCollection->getFirstItem();
        $quoteItem->setQty($productCartQty);
        $purchaseOrder->setSnapshotQuote($quote);
        $this->purchaseOrderRepository->save($purchaseOrder);

        // Dispatch the request to the view payment details page for the desired purchase order
        $this->dispatch(self::URI . '/purchaseOrderId/' . $purchaseOrderId);

        // Perform assertions
        $this->assertEquals($expectedHttpResponseCode, $this->getResponse()->getHttpResponseCode());

        if ($expectedRedirect) {
            $this->assertRedirect($this->stringContains($expectedRedirect));
        }

        $this->session->logout();
    }

    /**
     *
     * @magentoDataFixture Magento/PurchaseOrder/_files/company_with_structure_with_purchase_orders_and_online_payment_method_used.php
     */
    public function testDiscountCodeFieldRemovedOnPaymentDetails()
    {
        $this->setCompanyPurchaseOrderConfig('Magento', true);

        // Log in as the current user
        $purchaseOrderId = $this->getPurchaseOrderForCustomer('john.doe@example.com')->getEntityId();
        $currentUser = $this->customerRegistry->retrieveByEmail('john.doe@example.com');

        $this->getRequest()->setParam('purchaseOrderId', $purchaseOrderId);
        $this->session->setCustomerAsLoggedIn($currentUser);

        $purchaseOrder = $this->purchaseOrderRepository->getById($purchaseOrderId);
        $purchaseOrder->setStatus(PurchaseOrderInterface::STATUS_APPROVED_PENDING_PAYMENT);
        $this->purchaseOrderRepository->save($purchaseOrder);

        // Dispatch the request to the view payment details page for the desired purchase order
        $this->dispatch(self::URI . '/purchaseOrderId/' . $purchaseOrderId);

        $component = json_encode('Magento_SalesRule/js/view/payment/discount-messages');
        $this->assertStringNotContainsString(
            $component,
            $this->getResponse()->getBody()
        );
    }

    /**
     * Data provider for various view action scenarios for company users.
     *
     * @return array
     */
    public function paymentDetailsPageDataProvider()
    {
        return [
            'po_creator_with_po_pending_payment' => [
                'current_customer' => 'john.doe@example.com',
                'purchase_order_creator_email' => 'john.doe@example.com',
                'order_status' => PurchaseOrderInterface::STATUS_APPROVED_PENDING_PAYMENT,
                'expected_http_response_code' => 200,
                'expected_redirect' => '',
            ],
            'other_company_customer_with_po_pending_payment' => [
                'current_customer' => 'veronica.costello@example.com',
                'purchase_order_creator_email' => 'john.doe@example.com',
                'order_status' => PurchaseOrderInterface::STATUS_APPROVED_PENDING_PAYMENT,
                'expected_http_response_code' => 302,
                'expected_redirect' => 'company/accessdenied'
            ],
            'po_creator_with_po_approved' => [
                'current_customer' => 'john.doe@example.com',
                'purchase_order_creator_email' => 'john.doe@example.com',
                'order_status' => PurchaseOrderInterface::STATUS_APPROVED,
                'expected_http_response_code' => 302,
                'expected_redirect' => 'noroute',
            ]
        ];
    }

    /**
     * Data provider for various quote errors redirect.
     *
     * @return array
     */
    public function paymentDetailsPageQuoteErrorDataProvider()
    {
        return [
            'product_enabled_in_stock_right_item_qty' => [
                'current_customer' => 'john.doe@example.com',
                'purchase_order_creator_email' => 'john.doe@example.com',
                'order_status' => PurchaseOrderInterface::STATUS_APPROVED_PENDING_PAYMENT,
                'product_stock_status' => StockStatus::STATUS_IN_STOCK,
                'product_status' => ProductStatus::STATUS_ENABLED,
                'product_qty' => 10,
                'product_cart_qty' => 2,
                'expected_http_response_code' => 200,
                'expected_redirect' => '',
            ],
            'product_disabled_in_stock_right_item_qty' => [
                'current_customer' => 'john.doe@example.com',
                'purchase_order_creator_email' => 'john.doe@example.com',
                'order_status' => PurchaseOrderInterface::STATUS_APPROVED_PENDING_PAYMENT,
                'product_stock_status' => StockStatus::STATUS_IN_STOCK,
                'product_status' => ProductStatus::STATUS_DISABLED,
                'product_qty' => 10,
                'product_cart_qty' => 2,
                'expected_http_response_code' => 302,
                'expected_redirect' => 'purchaseorder/purchaseorder/view',
            ],
            'product_enabled_out_of_stock_right_item_qty' => [
                'current_customer' => 'john.doe@example.com',
                'purchase_order_creator_email' => 'john.doe@example.com',
                'order_status' => PurchaseOrderInterface::STATUS_APPROVED_PENDING_PAYMENT,
                'product_stock_status' => StockStatus::STATUS_OUT_OF_STOCK,
                'product_status' => ProductStatus::STATUS_ENABLED,
                'product_qty' => 10,
                'product_cart_qty' => 2,
                'expected_http_response_code' => 302,
                'expected_redirect' => 'purchaseorder/purchaseorder/view',
            ],
            'product_enabled_in_stock_zero_item_qty' => [
                'current_customer' => 'john.doe@example.com',
                'purchase_order_creator_email' => 'john.doe@example.com',
                'order_status' => PurchaseOrderInterface::STATUS_APPROVED_PENDING_PAYMENT,
                'product_stock_status' => StockStatus::STATUS_IN_STOCK,
                'product_status' => ProductStatus::STATUS_ENABLED,
                'product_qty' => 0,
                'product_cart_qty' => 2,
                'expected_http_response_code' => 302,
                'expected_redirect' => 'purchaseorder/purchaseorder/view',
            ],
            'product_enabled_in_stock_wrong_item_qty' => [
                'current_customer' => 'john.doe@example.com',
                'purchase_order_creator_email' => 'john.doe@example.com',
                'order_status' => PurchaseOrderInterface::STATUS_APPROVED_PENDING_PAYMENT,
                'product_stock_status' => StockStatus::STATUS_IN_STOCK,
                'product_status' => ProductStatus::STATUS_ENABLED,
                'product_qty' => 1,
                'product_cart_qty' => 2,
                'expected_http_response_code' => 302,
                'expected_redirect' => 'purchaseorder/purchaseorder/view',
            ]
        ];
    }

    /**
     * Get purchase order for the given customer.
     *
     * @param string $customerEmail
     * @return \Magento\PurchaseOrder\Api\Data\PurchaseOrderInterface
     */
    private function getPurchaseOrderForCustomer(string $customerEmail)
    {
        $customer = $this->customerRepository->get($customerEmail);
        $searchCriteria = $this->searchCriteriaBuilder
            ->addFilter(PurchaseOrderInterface::CREATOR_ID, $customer->getId())
            ->create();
        $purchaseOrders = $this->purchaseOrderRepository->getList($searchCriteria)->getItems();
        return array_shift($purchaseOrders);
    }
}
