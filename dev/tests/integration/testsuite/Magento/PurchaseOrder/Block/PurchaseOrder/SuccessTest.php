<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\PurchaseOrder\Block\PurchaseOrder;

use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\OfflinePayments\Model\Checkmo;
use Magento\Paypal\Model\Config;
use Magento\PurchaseOrder\Api\PurchaseOrderRepositoryInterface;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\ObjectManager;
use PHPUnit\Framework\TestCase;
use Magento\PurchaseOrder\ViewModel\PurchaseOrder\Success as SuccessViewModel;

/**
 * Block test class for purchase order success page
 *
 * @see \Magento\PurchaseOrder\Block\PurchaseOrder\Success
 *
 * @magentoAppArea frontend
 */
class SuccessTest extends TestCase
{
    /**
     * @var PurchaseOrderRepositoryInterface
     */
    private $purchaseOrderRepository;

    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @var Success
     */
    private $successBlock;

    /**
     * @var SuccessViewModel
     */
    private $viewModel;

    /**
     * @inheriDoc
     */
    protected function setUp(): void
    {
        /** @var ObjectManager $objectManager */
        $objectManager = Bootstrap::getObjectManager();
        $this->purchaseOrderRepository = $objectManager->get(PurchaseOrderRepositoryInterface::class);
        $this->searchCriteriaBuilder = $objectManager->get(SearchCriteriaBuilder::class);
        $this->successBlock = $objectManager->get(Success::class);
        $this->viewModel = $objectManager->get(SuccessViewModel::class);
    }

    /**
     * Test that the payment method set for purchase order is offline
     *
     * @magentoDataFixture Magento/PurchaseOrder/_files/purchase_orders.php
     */
    public function testIsPaymentMethodOffline()
    {
        $purchaseOrder = $this->getPurchaseOrderByIncrementId(900000001);
        $purchaseOrder->setPaymentMethod(Checkmo::PAYMENT_METHOD_CHECKMO_CODE);
        $this->purchaseOrderRepository->save($purchaseOrder);

        $this->successBlock->setViewModel($this->viewModel);
        /** @var \Magento\PurchaseOrder\ViewModel\PurchaseOrder\Success $viewModel */
        $viewModel = $this->successBlock->getViewModel();
        $this->assertFalse($viewModel->getPaymentStrategy()->isDeferredPayment($purchaseOrder));
    }

    /**
     * Test that the payment method set for purchase order is not offline
     *
     * @magentoDataFixture Magento/PurchaseOrder/_files/purchase_orders.php
     */
    public function testIsPaymentMethodNotOffline()
    {
        $purchaseOrder = $this->getPurchaseOrderByIncrementId(900000001);
        $purchaseOrder->setPaymentMethod(Config::METHOD_BILLING_AGREEMENT);
        $this->purchaseOrderRepository->save($purchaseOrder);

        $this->successBlock->setViewModel($this->viewModel);
        /** @var \Magento\PurchaseOrder\ViewModel\PurchaseOrder\Success $viewModel */
        $viewModel = $this->successBlock->getViewModel();
        $this->assertTrue($viewModel->getPaymentStrategy()->isDeferredPayment($purchaseOrder));
    }

    /**
     * Get purchase order by increment id
     *
     * @param int $incrementId
     * @return mixed
     */
    private function getPurchaseOrderByIncrementId(int $incrementId)
    {
        $searchCriteria = $this->searchCriteriaBuilder->addFilter('increment_id', $incrementId)->create();
        return current($this->purchaseOrderRepository->getList($searchCriteria)->getItems());
    }
}
