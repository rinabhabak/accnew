<?php
/**
 * Copyright (c) 2015 S.L.I. Systems, Inc. (www.sli-systems.com) - All Rights Reserved
 * This file is part of Learning Search Connect.
 * Learning Search Connect is distributed under a limited and restricted
 * license – please visit www.sli-systems.com/LSC for full license details.
 *
 * THIS CODE AND INFORMATION ARE PROVIDED "AS IS" WITHOUT WARRANTY OF ANY
 * KIND, EITHER EXPRESSED OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE
 * IMPLIED WARRANTIES OF MERCHANTABILITY AND/OR FITNESS FOR A
 * PARTICULAR PURPOSE. TO THE MAXIMUM EXTENT PERMITTED BY APPLICABLE LAW, IN NO
 * EVENT WILL SLI BE LIABLE TO YOU OR ANY OTHER PARTY FOR ANY GENERAL, DIRECT,
 * INDIRECT, SPECIAL, INCIDENTAL OR CONSEQUENTIAL LOSS OR DAMAGES OF ANY
 * CHARACTER ARISING OUT OF THE USE OF THE CODE AND/OR THE LICENSE INCLUDING
 * BUT NOT LIMITED TO PERSONAL INJURY, LOSS OF DATA, LOSS OF PROFITS, LOSS OF
 * ASSIGNMENTS, DATA OR OUTPUT FROM THE SERVICE BEING RENDERED INACCURATE,
 * FAILURE OF CODE, SERVER DOWN TIME, DAMAGES FOR LOSS OF GOODWILL, BUSINESS
 * INTERRUPTION, COMPUTER FAILURE OR MALFUNCTION, OR ANY AND ALL OTHER DAMAGES
 * OR LOSSES OF WHATEVER NATURE, EVEN IF SLI HAS BEEN INFORMED OF THE
 * POSSIBILITY OF SUCH DAMAGES.
 */

namespace SLI\Feed\Test\Integration\Model;

use DOMDocument;
use DOMXPath;
use Magento\Framework\DB\Select;
use Magento\Framework\ObjectManagerInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\TestFramework\Helper\Bootstrap;
use SLI\Feed\Helper\XmlWriter;

/**
 * Class PriceFeedTest
 * @package SLI\Feed\Test\Integration\Model
 *
 * Reference pages C:/magento_source/archive/magento2/dev/tests/integration/testsuite/Magento/Catalog/Console/Command/ProductAttributesCleanUpTest.php
 * http://devdocs.magento.com/guides/v2.1/test/integration/integration_test_execution_cli.html
 *
 * C:\magento\magento2\dev\tests\integration\testsuite\Magento\CatalogImportExport\Model\AbstractProductExportImportTestCase.php
 */
class PriceFeedTest extends \PHPUnit\Framework\TestCase
{

    const PRICE_RULE_SELECTOR = '//advanced_pricing/product_pricing';
    const STORE_ID = '1';

    /**
     * @var ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * @var DOMXPath
     */
    protected $xpath;

    public static function loadFixtureHasCatalogPriceRule()
    {
        require __DIR__ . '/../_files/hasCatalogPriceRule.php';
    }

    public static function loadFixtureHasTierPrice()
    {
        require __DIR__ . '/../_files/hasTierPrice.php';
    }

    public function setUp() {

        $this->objectManager = Bootstrap::getObjectManager();

        /** @var \Magento\Framework\App\Config\MutableScopeConfigInterface $mutableConfig */
        $mutableConfig = $this->objectManager->get('Magento\Framework\App\Config\MutableScopeConfigInterface');
        $mutableConfig->setValue('sli_feed_generation/general/enabled', '1', ScopeInterface::SCOPE_STORE);
        $mutableConfig->setValue('sli_feed_generation/product/attributes_select', '{}', ScopeInterface::SCOPE_STORE);
        $mutableConfig->setValue('sli_feed_generation/general/log_level', 'error', ScopeInterface::SCOPE_STORE);
        $mutableConfig->setValue('sli_feed_generation/feed/include_out_of_stock', '1', ScopeInterface::SCOPE_STORE);
        $mutableConfig->setValue('sli_feed_generation/ftp/enabled', '0', ScopeInterface::SCOPE_STORE);
        $mutableConfig->setValue('sli_feed_generation/feed/advanced_pricing', '1', ScopeInterface::SCOPE_STORE);
    }

    /**
     * @magentoDataFixture loadFixtureHasCatalogPriceRule
     * @magentoDbIsolation enabled
     * @magentoAppIsolation enabled
     */
    public function testHasCatalogPriceRule() {

        $this->generateFeed();

        $priceSelector = self::PRICE_RULE_SELECTOR . '[id="1"]/catalog_price_rules';
        $expectedValues = [
            'price/customer_name' => 'NOT LOGGED IN',
            'price/final_price' => '5.0000',
            'price/price' => '10.0000'
        ];
        $productPricing = $this->xpath->query($priceSelector);
        $this->assertCount(1, $productPricing, 'Could not find a price rule.');

        $this->assertValues($priceSelector, $expectedValues);
        $this->assertGreaterThanOrEqual(1,$this->xpath->query(self::PRICE_RULE_SELECTOR)->length);
    }

    /**
     * @magentoDataFixture loadFixtureHasTierPrice
     * @magentoDbIsolation enabled
     * @magentoAppIsolation enabled
     */
    public function testHasTierPrice() {

        $this->generateFeed();

        $priceSelector = self::PRICE_RULE_SELECTOR . '[id="1"]/tier_prices';
        $expectedValues = [
            'price/cust_name' => 'NOT LOGGED IN',
            'price/price_qty' => '3.0000',
            'price/price' => '8.0000'
        ];
        $productPricing = $this->xpath->query($priceSelector);
        $this->assertCount(1, $productPricing, 'Could not find a tier pricing.');

        $this->assertValues($priceSelector, $expectedValues);
        $this->assertGreaterThanOrEqual(1,$this->xpath->query(self::PRICE_RULE_SELECTOR)->length);
    }

    protected function generateFeed() {

        $productEntityCollectionFactory = $this->objectManager->get(
            '\Magento\Catalog\Model\ResourceModel\Product\CollectionFactory'
        );
        $productEntityCollection = $productEntityCollectionFactory->create();

        $catalogRuleFactory = $this->objectManager->get(
            '\Magento\CatalogRule\Model\ResourceModel\RuleFactory'
        );

        $catalogRule = $catalogRuleFactory->create();

        $productEntityCollection
            ->setStoreId(self::STORE_ID)
            ->addAttributeToSelect('*')
            ->addCategoryIds()
            ->setOrder('entity_id', Select::SQL_ASC);
        $productEntityCollection = $productEntityCollection
            ->addUrlRewrite();

        $loggerFactory = $this->objectManager->get('\SLI\Feed\Logging\LoggerFactoryInterface');
        $logger = $loggerFactory->getStoreLogger(self::STORE_ID);

        $generatorHelper = $this->objectManager->get('\SLI\Feed\Helper\GeneratorHelper');
        $extraAttributes = $generatorHelper->getAttributes(self::STORE_ID, $logger);
        $feedFilename = sprintf($generatorHelper->getFeedFileTemplate(), '1');
        $xmlWriter = new XmlWriter($feedFilename);

        $productGenerator = $this->objectManager->get('\SLI\Feed\Model\Generators\ProductGenerator');
        $productGenerator->writeCollection($productEntityCollection, $extraAttributes, self::STORE_ID, $xmlWriter, $logger);

        $priceGenerator = $this->objectManager->get('\SLI\Feed\Model\Generators\PriceGenerator');
        $priceGenerator->writeCollection($productEntityCollection, self::STORE_ID, $xmlWriter, $logger, $catalogRule);

        $xmlWriter->closeFeed();

        // TODO This is the slowest part reading in the whole file. Work out a way around this.
        $xml = new DomDocument;
        $xml->load($feedFilename);
        $this->xpath = new DOMXPath($xml);
    }

    /**
     * @param $productSelector
     * @param $expectedValues
     */
    protected function assertValues($productSelector, $expectedValues) {
        foreach ($expectedValues as $selector => $expectedValue) {
            $selector = $productSelector . '/' .  $selector;
            $nodeList = $this->xpath->query($selector);
            $this->assertCount(1, $nodeList);
            $this->assertEquals($expectedValue, $nodeList->item(0)->nodeValue, sprintf('Could not find "%s" => "%s"', $selector, $expectedValue));
        }
    }
}
