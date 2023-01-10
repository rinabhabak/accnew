<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CompanyShipping\Controller\Adminhtml\Order;

use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Backend\Model\Session\Quote as SessionQuote;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Exception\AuthenticationException;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Framework\App\Request\Http as HttpRequest;
use Magento\Store\Model\ScopeInterface;
use Magento\Company\Api\CompanyRepositoryInterface;
use Magento\CompanyShipping\Model\CompanyShippingMethodFactory;
use Magento\CompanyShipping\Model\Source\CompanyApplicableShippingMethod;

/**
 * Test Class for B2B shipping method settings by admin create order flow with selected shipping methods
 *
 * @magentoAppArea adminhtml
 * @magentoDbIsolation enabled
 */
class CreateWithSelectedShippingMethodsTest extends CreateAbstract
{
    /**
     * @inheritDoc
     *
     * @throws AuthenticationException
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->companyShippingMethodFactory = $this->_objectManager->get(CompanyShippingMethodFactory::class);
    }

    /**
     * @var CompanyShippingMethodFactory
     */
    private $companyShippingMethodFactory;

    /**
     * Test available shipping rates for non company customer quote by admin create order with:
     * B2B applicable shipping methods enabled
     * B2B applicable shipping methods is: free shipping
     * Global sales shipping methods are: free shipping, flat rate, table rate
     *
     * @param array $configData
     *
     * @magentoDataFixture Magento/Checkout/_files/quote_with_address_saved.php
     * @magentoDataFixture Magento/OfflineShipping/_files/tablerates.php
     * @dataProvider shippingConfigDataProviderWithSelectedShippingMethodsEnabled
     * @throws \Exception
     */
    public function testLoadBlockShippingMethodWithNonCompanyCustomerWithB2BApplicableShippingMethod(
        $configData
    ) {
        $this->setConfigValues($configData);
        $quote = $this->_objectManager->get(CartRepositoryInterface::class)->getForCustomer(1);
        $customer = $quote->getCustomer();
        $session = $this->_objectManager->get(SessionQuote::class);
        $session->setQuoteId($quote->getId());

        $this->getRequest()->setMethod(HttpRequest::METHOD_POST);
        $this->getRequest()->setPostValue(
            [
                'customer_id' => $customer->getId(),
                'collect_shipping_rates' => 1,
                'store_id' => 1,
                'json' => true
            ]
        );
        $this->dispatch('backend/sales/order_create/loadBlock/block/shipping_method');
        $body = $this->getResponse()->getBody();

        self::assertStringContainsString('freeshipping_freeshipping', $body);
        self::assertStringContainsString('flatrate_flatrate', $body);
        self::assertStringContainsString('tablerate_bestway', $body);
    }

    /**
     * Test available shipping rates for company customer quote by admin create order with:
     * Company B2B shipping methods is B2BShippingMethods
     * B2B settings selected shipping methods enabled
     * B2B settings selected shipping methods are: free shipping, table rate
     * Global sales shipping methods are: free shipping, flat rate shipping
     *
     * @param array $configData
     *
     * @magentoDataFixture Magento/Company/_files/company_with_structure.php
     * @magentoDataFixture Magento/Checkout/_files/quote_with_address_saved.php
     * @magentoDataFixture Magento/OfflineShipping/_files/tablerates.php
     * @dataProvider shippingConfigDataProviderWithSelectedShippingMethodsEnabled
     * @throws \Exception
     */
    public function testLoadBlockShippingMethodWithCompanyCustomerB2BShippingMethodsAndSelectedShippingMethods(
        $configData
    ) {
        $this->setConfigValues($configData);
        $quote = $this->_objectManager->get(CartRepositoryInterface::class)->getForCustomer(1);
        //replace quote customer to company customer
        $companyCustomer = $this->_objectManager->get(CustomerRepositoryInterface::class)
            ->get('alex.smith@example.com');

        $company = $this->_objectManager->get(CompanyRepositoryInterface::class)->get(
            $companyCustomer->getExtensionAttributes()->getCompanyAttributes()->getCompanyId()
        );

        $companyShippingSettings = $this->companyShippingMethodFactory->create()->addData(
            [
                'company_id' => $company->getId(),
                'applicable_shipping_method' => CompanyApplicableShippingMethod::B2B_SHIPPING_METHODS_VALUE,
                'use_config_settings' => 0
            ]
        );
        $companyShippingSettings->save();

        $quote->setCustomerId($companyCustomer->getId());
        $quote->setCustomer($companyCustomer);
        $quote->save();

        $session = $this->_objectManager->get(SessionQuote::class);
        $session->setQuoteId($quote->getId());

        $this->getRequest()->setMethod(HttpRequest::METHOD_POST);
        $this->getRequest()->setPostValue(
            [
                'customer_id' => $companyCustomer->getId(),
                'collect_shipping_rates' => 1,
                'store_id' => 1,
                'json' => true
            ]
        );
        $this->dispatch('backend/sales/order_create/loadBlock/block/shipping_method');
        $body = $this->getResponse()->getBody();

        self::assertStringContainsString('freeshipping_freeshipping', $body);
        self::assertStringContainsString('tablerate_bestway', $body);
        self::assertStringNotContainsString('flatrate_flatrate', $body);
    }

    /**
     * Test available shipping rates for company customer quote by admin create order with:
     * Company B2B shipping methods is ALL Shipping Methods
     * B2B settings selected shipping methods enabled
     * B2B settings selected shipping methods are: free shipping, table rate
     * Global sales shipping methods are: free shipping, flat rate shipping
     *
     * @param array $configData
     *
     * @magentoDataFixture Magento/Company/_files/company_with_structure.php
     * @magentoDataFixture Magento/Checkout/_files/quote_with_address_saved.php
     * @magentoDataFixture Magento/OfflineShipping/_files/tablerates.php
     * @dataProvider shippingConfigDataProviderWithSelectedShippingMethodsEnabled
     * @throws \Exception
     */
    public function testLoadBlockShippingMethodWithCompanyCustomerAllShippingMethodsAndSelectedShippingMethods(
        $configData
    ) {
        $this->setConfigValues($configData);
        $quote = $this->_objectManager->get(CartRepositoryInterface::class)->getForCustomer(1);
        //replace quote customer to company customer
        $companyCustomer = $this->_objectManager->get(CustomerRepositoryInterface::class)
            ->get('alex.smith@example.com');

        $company = $this->_objectManager->get(CompanyRepositoryInterface::class)->get(
            $companyCustomer->getExtensionAttributes()->getCompanyAttributes()->getCompanyId()
        );

        $companyShippingSettings = $this->companyShippingMethodFactory->create()->addData(
            [
                'company_id' => $company->getId(),
                'applicable_shipping_method' => CompanyApplicableShippingMethod::ALL_SHIPPING_METHODS_VALUE,
                'use_config_settings' => 0
            ]
        );
        $companyShippingSettings->save();

        $quote->setCustomerId($companyCustomer->getId());
        $quote->setCustomer($companyCustomer);
        $quote->save();

        $session = $this->_objectManager->get(SessionQuote::class);
        $session->setQuoteId($quote->getId());

        $this->getRequest()->setMethod(HttpRequest::METHOD_POST);
        $this->getRequest()->setPostValue(
            [
                'customer_id' => $companyCustomer->getId(),
                'collect_shipping_rates' => 1,
                'store_id' => 1,
                'json' => true
            ]
        );
        $this->dispatch('backend/sales/order_create/loadBlock/block/shipping_method');
        $body = $this->getResponse()->getBody();

        self::assertStringContainsString('freeshipping_freeshipping', $body);
        self::assertStringContainsString('tablerate_bestway', $body);
        self::assertStringContainsString('flatrate_flatrate', $body);
    }

    /**
     * Test available shipping rates for company customer quote by admin create order with:
     * Company B2B shipping methods is default
     * B2B settings selected shipping methods enabled
     * B2B settings selected shipping methods are: free shipping, table rate
     * Global sales shipping methods are: free shipping, flat rate shipping, table rate
     *
     * @param array $configData
     *
     * @magentoDataFixture Magento/Company/_files/company_with_structure.php
     * @magentoDataFixture Magento/Checkout/_files/quote_with_address_saved.php
     * @magentoDataFixture Magento/OfflineShipping/_files/tablerates.php
     * @dataProvider shippingConfigDataProviderWithSelectedShippingMethodsEnabled
     * @throws \Exception
     */
    public function testLoadBlockShippingMethodWithCompanyCustomerDefaultB2BShippingMethodsAndSelectedShippingMethods(
        $configData
    ) {
        $this->setConfigValues($configData);
        $quote = $this->_objectManager->get(CartRepositoryInterface::class)->getForCustomer(1);
        //replace quote customer to company customer
        $companyCustomer = $this->_objectManager->get(CustomerRepositoryInterface::class)
            ->get('alex.smith@example.com');

        $quote->setCustomerId($companyCustomer->getId());
        $quote->setCustomer($companyCustomer);
        $quote->save();

        $session = $this->_objectManager->get(SessionQuote::class);
        $session->setQuoteId($quote->getId());

        $this->getRequest()->setMethod(HttpRequest::METHOD_POST);
        $this->getRequest()->setPostValue(
            [
                'customer_id' => $companyCustomer->getId(),
                'collect_shipping_rates' => 1,
                'store_id' => 1,
                'json' => true
            ]
        );
        $this->dispatch('backend/sales/order_create/loadBlock/block/shipping_method');
        $body = $this->getResponse()->getBody();

        self::assertStringContainsString('freeshipping_freeshipping', $body);
        self::assertStringContainsString('tablerate_bestway', $body);
        self::assertStringNotContainsString('flatrate_flatrate', $body);
    }

    /**
     * Test available shipping rates for non company quote by admin create order with:
     * Company B2B shipping methods is B2BShippingMethods
     * B2B settings selected shipping methods enabled
     * B2B settings selected shipping methods are: free shipping, table rate
     * Global sales shipping methods are: free shipping, flat rate, table rate
     *
     * @param array $configData
     *
     * @magentoDataFixture Magento/Company/_files/company_with_structure.php
     * @magentoDataFixture Magento/Checkout/_files/quote_with_address_saved.php
     * @magentoDataFixture Magento/OfflineShipping/_files/tablerates.php
     * @dataProvider shippingConfigDataProviderWithSelectedShippingMethodsEnabled
     * @throws \Exception
     */
    public function testLoadBlockShippingMethodWithNonCompanyCustomerB2BShippingMethodsAndSelectedShippingMethods(
        $configData
    ) {
        $this->setConfigValues($configData);
        $quote = $this->_objectManager->get(CartRepositoryInterface::class)->getForCustomer(1);
        //replace quote customer to company customer
        $companyCustomer = $this->_objectManager->get(CustomerRepositoryInterface::class)
            ->get('alex.smith@example.com');

        $company = $this->_objectManager->get(CompanyRepositoryInterface::class)->get(
            $companyCustomer->getExtensionAttributes()->getCompanyAttributes()->getCompanyId()
        );

        $companyShippingSettings = $this->companyShippingMethodFactory->create()->addData(
            [
                'company_id' => $company->getId(),
                'applicable_shipping_method' => CompanyApplicableShippingMethod::B2B_SHIPPING_METHODS_VALUE,
                'use_config_settings' => 0
            ]
        );
        $companyShippingSettings->save();

        $session = $this->_objectManager->get(SessionQuote::class);
        $session->setQuoteId($quote->getId());

        $this->getRequest()->setMethod(HttpRequest::METHOD_POST);
        $this->getRequest()->setPostValue(
            [
                'customer_id' => 1,
                'collect_shipping_rates' => 1,
                'store_id' => 1,
                'json' => true
            ]
        );
        $this->dispatch('backend/sales/order_create/loadBlock/block/shipping_method');
        $body = $this->getResponse()->getBody();

        self::assertStringContainsString('freeshipping_freeshipping', $body);
        self::assertStringContainsString('tablerate_bestway', $body);
        self::assertStringContainsString('flatrate_flatrate', $body);
    }

    /**
     * Test available shipping rates for company customer quote by admin create order with:
     * Company B2B shipping methods is selected shipping methods: free shipping
     * B2B settings selected shipping methods enabled
     * B2B settings selected shipping methods are: free shipping, table rate
     * Global sales shipping methods are: free shipping, flat rate shipping
     *
     * @param array $configData
     *
     * @magentoDataFixture Magento/Company/_files/company_with_structure.php
     * @magentoDataFixture Magento/Checkout/_files/quote_with_address_saved.php
     * @magentoDataFixture Magento/OfflineShipping/_files/tablerates.php
     * @dataProvider shippingConfigDataProviderWithSelectedShippingMethodsEnabled
     * @throws \Exception
     */
    public function testLoadBlockShippingMethodWithCompanyCustomerSelectedShippingMethodsAndB2BSelectedShippingMethods(
        $configData
    ) {
        $this->setConfigValues($configData);
        $quote = $this->_objectManager->get(CartRepositoryInterface::class)->getForCustomer(1);
        //replace quote customer to company customer
        $companyCustomer = $this->_objectManager->get(CustomerRepositoryInterface::class)
            ->get('alex.smith@example.com');

        $company = $this->_objectManager->get(CompanyRepositoryInterface::class)->get(
            $companyCustomer->getExtensionAttributes()->getCompanyAttributes()->getCompanyId()
        );

        $companyShippingSettings = $this->companyShippingMethodFactory->create()->addData(
            [
                'company_id' => $company->getId(),
                'applicable_shipping_method' => CompanyApplicableShippingMethod::SELECTED_SHIPPING_METHODS_VALUE,
                'available_shipping_methods' => 'freeshipping',
                'use_config_settings' => 0
            ]
        );
        $companyShippingSettings->save();

        $quote->setCustomerId($companyCustomer->getId());
        $quote->setCustomer($companyCustomer);
        $quote->save();

        $session = $this->_objectManager->get(SessionQuote::class);
        $session->setQuoteId($quote->getId());

        $this->getRequest()->setMethod(HttpRequest::METHOD_POST);
        $this->getRequest()->setPostValue(
            [
                'customer_id' => $companyCustomer->getId(),
                'collect_shipping_rates' => 1,
                'store_id' => 1,
                'json' => true
            ]
        );
        $this->dispatch('backend/sales/order_create/loadBlock/block/shipping_method');
        $body = $this->getResponse()->getBody();

        self::assertStringContainsString('freeshipping_freeshipping', $body);
        self::assertStringNotContainsString('tablerate_bestway', $body);
        self::assertStringNotContainsString('flatrate_flatrate', $body);
    }

    /**
     * Test available shipping rates for different company customer quote by admin create order with:
     * Company B2B shipping methods is selected shipping methods: free shipping
     * B2B settings selected shipping methods enabled
     * B2B settings selected shipping methods are: free shipping, table rate
     * Global sales shipping methods are: free shipping, flat rate shipping
     *
     * @param array $configData
     *
     * @magentoDataFixture Magento/Company/_files/company_with_structure.php
     * @magentoDataFixture Magento/Company/_files/company.php
     * @magentoDataFixture Magento/Checkout/_files/quote_with_address_saved.php
     * @magentoDataFixture Magento/OfflineShipping/_files/tablerates.php
     * @dataProvider shippingConfigDataProviderWithSelectedShippingMethodsEnabled
     * @throws \Exception
     */
    public function testLoadBlockShippingMethodWithDiffCustomerB2BShippingMethodsAndB2BSelectedShippingMethods(
        $configData
    ) {
        $this->setConfigValues($configData);
        $quote = $this->_objectManager->get(CartRepositoryInterface::class)->getForCustomer(1);
        //replace quote customer to company customer
        $companyCustomer = $this->_objectManager->get(CustomerRepositoryInterface::class)
            ->get('alex.smith@example.com');
        $diffCompanyCustomer = $this->_objectManager->get(CustomerRepositoryInterface::class)->get('admin@magento.com');

        $company = $this->_objectManager->get(CompanyRepositoryInterface::class)->get(
            $companyCustomer->getExtensionAttributes()->getCompanyAttributes()->getCompanyId()
        );

        $companyShippingSettings = $this->companyShippingMethodFactory->create()->addData(
            [
                'company_id' => $company->getId(),
                'applicable_shipping_method' => CompanyApplicableShippingMethod::SELECTED_SHIPPING_METHODS_VALUE,
                'available_shipping_methods' => 'freeshipping',
                'use_config_settings' => 0
            ]
        );
        $companyShippingSettings->save();

        $quote->setCustomerId($diffCompanyCustomer->getId());
        $quote->setCustomer($diffCompanyCustomer);
        $quote->save();

        $session = $this->_objectManager->get(SessionQuote::class);
        $session->setQuoteId($quote->getId());

        $this->getRequest()->setMethod(HttpRequest::METHOD_POST);
        $this->getRequest()->setPostValue(
            [
                'customer_id' => $diffCompanyCustomer->getId(),
                'collect_shipping_rates' => 1,
                'store_id' => 1,
                'json' => true
            ]
        );
        $this->dispatch('backend/sales/order_create/loadBlock/block/shipping_method');
        $body = $this->getResponse()->getBody();

        self::assertStringContainsString('freeshipping_freeshipping', $body);
        self::assertStringContainsString('tablerate_bestway', $body);
        self::assertStringNotContainsString('flatrate_flatrate', $body);
    }

    /**
     * Config data provider with B2B Selected Shipping Methods Enabled
     * @return array
     */
    public function shippingConfigDataProviderWithSelectedShippingMethodsEnabled()
    {
        return [
            'defaultScope' => [
                'config_data' => [
                    ScopeConfigInterface::SCOPE_TYPE_DEFAULT => [
                        '' => [
                            'carriers/flatrate/active' => '1',
                            'carriers/freeshipping/active' => '1',
                            'carriers/tablerate/active' => '1',
                            'carriers/tablerate/condition_name' => 'package_qty',
                            'btob/default_b2b_shipping_methods/applicable_shipping_methods' => 1,
                            'btob/default_b2b_shipping_methods/available_shipping_methods' => 'freeshipping,tablerate',
                            'btob/order_approval/purchaseorder_active' => 0
                        ]
                    ],
                ]
            ],
            'defaultScopeWithPurchaseOrderEnabled' => [
                'config_data' => [
                    ScopeConfigInterface::SCOPE_TYPE_DEFAULT => [
                        '' => [
                            'carriers/flatrate/active' => '1',
                            'carriers/freeshipping/active' => '1',
                            'carriers/tablerate/active' => '1',
                            'carriers/tablerate/condition_name' => 'package_qty',
                            'btob/default_b2b_shipping_methods/applicable_shipping_methods' => 1,
                            'btob/default_b2b_shipping_methods/available_shipping_methods' => 'freeshipping,tablerate',
                            'btob/order_approval/purchaseorder_active' => 1
                        ]
                    ],
                ]
            ],
            'websiteScope' => [
                'config_data' => [
                    ScopeConfigInterface::SCOPE_TYPE_DEFAULT => [
                        '' => [
                            'carriers/flatrate/active' => '0',
                            'carriers/freeshipping/active' => '0',
                            'carriers/tablerate/active' => '0',
                            'carriers/tablerate/condition_name' => 'package_qty',
                            'btob/default_b2b_shipping_methods/applicable_shipping_methods' => 1,
                            'btob/default_b2b_shipping_methods/available_shipping_methods' => 'freeshipping,tablerate',
                            'btob/order_approval/purchaseorder_active' => 0
                        ]
                    ],
                    ScopeInterface::SCOPE_WEBSITES => [
                        'base' => [
                            'carriers/flatrate/active' => '1',
                            'carriers/freeshipping/active' => '1',
                            'carriers/tablerate/active' => '1',
                            'carriers/tablerate/condition_name' => 'package_qty',
                        ]
                    ],
                ]
            ],
            'websiteScopeWithPurchaseOrderEnabled' => [
                'config_data' => [
                    ScopeConfigInterface::SCOPE_TYPE_DEFAULT => [
                        '' => [
                            'carriers/flatrate/active' => '0',
                            'carriers/freeshipping/active' => '0',
                            'carriers/tablerate/active' => '0',
                            'carriers/tablerate/condition_name' => 'package_qty',
                            'btob/default_b2b_shipping_methods/applicable_shipping_methods' => 1,
                            'btob/default_b2b_shipping_methods/available_shipping_methods' => 'freeshipping,tablerate',
                            'btob/order_approval/purchaseorder_active' => 1
                        ]
                    ],
                    ScopeInterface::SCOPE_WEBSITES => [
                        'base' => [
                            'carriers/flatrate/active' => '1',
                            'carriers/freeshipping/active' => '1',
                            'carriers/tablerate/active' => '1',
                            'carriers/tablerate/condition_name' => 'package_qty',
                        ]
                    ],
                ]
            ]
        ];
    }
}
