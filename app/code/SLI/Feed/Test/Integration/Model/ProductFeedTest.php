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
use Magento\Framework\App\Config\MutableScopeConfigInterface;
use Magento\Framework\DB\Select;
use Magento\Framework\ObjectManagerInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\TestFramework\Helper\Bootstrap;
use SLI\Feed\Helper\XmlWriter;

/**
 * Class ProductFeedTest
 * @package SLI\Feed\Test\Integration\Model
 *
 * Reference pages C:/magento_source/archive/magento2/dev/tests/integration/testsuite/Magento/Catalog/Console/Command/ProductAttributesCleanUpTest.php
 * http://devdocs.magento.com/guides/v2.1/test/integration/integration_test_execution_cli.html
 *
 * C:\magento\magento2\dev\tests\integration\testsuite\Magento\CatalogImportExport\Model\AbstractProductExportImportTestCase.php
 *
 */
class ProductFeedTest extends \PHPUnit\Framework\TestCase {

    const PRODUCT_SELECTOR = '//products/product';
    const STORE_ID = '1';

    /**
     * @var MutableScopeConfigInterface
     */
    protected $mutableConfig;

    /**
     * @var ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * @var DOMXPath
     */
    protected $xpath;

    public static function loadFixtureSimpleProduct()
    {
        require __DIR__ . '/../_files/simpleProduct.php';
    }

    public static function loadFixtureProductWithImage()
    {
        require __DIR__ . '/../_files/productWithImage.php';
    }

    public function setUp() {

        $this->objectManager = Bootstrap::getObjectManager();

        /** @var \Magento\Framework\App\Config\MutableScopeConfigInterface $mutableConfig */
        $this->mutableConfig = $this->objectManager->get('Magento\Framework\App\Config\MutableScopeConfigInterface');
        $this->mutableConfig->setValue('sli_feed_generation/general/enabled', '1', ScopeInterface::SCOPE_STORE);
        $this->mutableConfig->setValue('sli_feed_generation/product/attributes_select', '{}', ScopeInterface::SCOPE_STORE);
        $this->mutableConfig->setValue('sli_feed_generation/general/log_level', 'error', ScopeInterface::SCOPE_STORE);
        $this->mutableConfig->setValue('sli_feed_generation/feed/include_out_of_stock', '0', ScopeInterface::SCOPE_STORE);
        $this->mutableConfig->setValue('sli_feed_generation/ftp/enabled', '0', ScopeInterface::SCOPE_STORE);
        $this->mutableConfig->setValue('sli_feed_generation/feed/advanced_pricing', '0', ScopeInterface::SCOPE_STORE);
    }

    /**
     * @magentoDataFixture loadFixtureSimpleProduct
     * @magentoDbIsolation enabled
     * @magentoAppIsolation enabled
     */
    public function testSimpleProduct() {

        $this->generateFeed();

        $productSelector = self::PRODUCT_SELECTOR . '[@sku="simple"]';
        $expectedValues = [
            'sku' => 'simple',
            'name' => 'Simple Product',
            'price' => '10.0000'
        ];
        $this->assertValues($productSelector, $expectedValues);
    }

    /**
     * @magentoDataFixture Magento/Catalog/_files/product_special_price.php
     * @magentoDbIsolation enabled
     * @magentoAppIsolation enabled
     */
    public function testSpecialPriceProduct() {

        $this->generateFeed();

        $productSelector = self::PRODUCT_SELECTOR . '[@sku="simple"]';
        $expectedValues = [
            'sku' => 'simple',
            'name' => 'Simple Product',
            'price' => '10.0000',
            'special_price' => '5.9900'
        ];
        $this->assertValues($productSelector, $expectedValues);
    }

    /**
     * @magentoDataFixture Magento/Catalog/_files/products_in_category.php
     * @magentoDbIsolation enabled
     * @magentoAppIsolation enabled
     */
    public function testInCategoryProduct() {

        $this->generateFeed();

        $productSelector = self::PRODUCT_SELECTOR . '[@sku="simple"]';
        $expectedValues = [
            'sku' => 'simple',
            'name' => 'Simple Related Product'
        ];
        $this->assertValues($productSelector, $expectedValues);

        $categoryValueSelector = $productSelector . '/categories/value_1';
        $numberOfCategories = $this->xpath->query($categoryValueSelector)->length;
        $this->assertGreaterThanOrEqual(1, $numberOfCategories, 'Could not find category.');

        $categorySelector = '//categories/category[@id=333]';
        $categories = $this->xpath->query($categorySelector);
        $numberOfCategories = $categories->length;
        $this->assertEquals(1, $numberOfCategories, 'Could not find category in category feed.');

        $categoryName = $categories->item(0)->getAttribute('name');
        $this->assertEquals('Category 1', $categoryName, 'Could not find expected category in category feed.');
    }

    /**
     * @magentoDataFixture Magento/Catalog/_files/product_simple_with_url_key.php
     * @magentoDbIsolation enabled
     * @magentoAppIsolation enabled
     */
    public function testProductURL() {

        $this->generateFeed();

        $productSelector = self::PRODUCT_SELECTOR . '[@sku="simple1"]';
        $expectedValues = [
            'sku' => 'simple1',
            'url_key' => 'url-key',
            'request_path' => 'url-key.html',
            'name' => 'Simple Product'
        ];
        $this->assertValues($productSelector, $expectedValues);

        $productSelector = self::PRODUCT_SELECTOR . '[@sku="simple2"]';
        $expectedValues = [
            'sku' => 'simple2',
            'url_key' => 'url-key2',
            'request_path' => 'url-key2.html',
            'name' => 'Simple Product 2'
        ];
        $this->assertValues($productSelector, $expectedValues);
    }

    /**
     * @magentoDataFixture Magento/Catalog/_files/products_crosssell.php
     * @magentoDbIsolation enabled
     * @magentoAppIsolation enabled
     */
    public function testInCrosssellProduct() {

        $this->mutableConfig->setValue(
            'sli_feed_generation/product/attributes_select',
            '{"_1519159617695_695":{"attribute_code":"crosssell_products"}}',
            ScopeInterface::SCOPE_STORE
        );
        $this->generateFeed();

        $productSelector = self::PRODUCT_SELECTOR . '[@sku="simple"]';
        $expectedValues = [
            'sku' => 'simple',
            'name' => 'Simple Cross Sell'
        ];
        $this->assertValues($productSelector, $expectedValues);
        $crossSellId = $this->xpath->query($productSelector. '/entity_id')->item(0)->nodeValue;

        $productSelector = self::PRODUCT_SELECTOR . '[@sku="simple_with_cross"]';
        $expectedValues = [
            'sku' => 'simple_with_cross',
            'name' => 'Simple Product With Cross Sell'
        ];
        $this->assertValues($productSelector, $expectedValues);

        $crosssellSelector = $productSelector . '/crosssell_products/value_1';
        $crosssellItems = $this->xpath->query($crosssellSelector);
        $this->assertCount(1, $crosssellItems, 'Did not find 1 crosssell product');

        $crosssellProductId = $crosssellItems->item(0)->nodeValue;
        $this->assertEquals($crossSellId, $crosssellProductId, 'Could not find expected crosssell product');
    }

    /**
     * @magentoDataFixture Magento/Catalog/_files/products_with_multiselect_attribute.php
     * @magentoDbIsolation enabled
     * @magentoAppIsolation enabled
     */
    public function testMultiselectAttribute() {

        $this->mutableConfig->setValue(
            'sli_feed_generation/product/attributes_select',
            '{"_1543984655894_894":{"attribute_code":"multiselect_attribute"}}',
            ScopeInterface::SCOPE_STORE
        );
        $this->generateFeed();

        $productSelector = self::PRODUCT_SELECTOR . '[@sku="simple_ms_1"]';
        $expectedValues = [
            'sku' => 'simple_ms_1',
            'name' => 'With Multiselect 1'
        ];
        $this->assertValues($productSelector, $expectedValues);

        $productSelector = self::PRODUCT_SELECTOR . '[@sku="simple_ms_2"]';
        $expectedValues = ['sku' => 'simple_ms_2'];
        $this->assertValues($productSelector, $expectedValues);

        $multiselectIdSelector = $productSelector . '/entity_id';
        $productId = $this->xpath->query($multiselectIdSelector)->item(0)->nodeValue;
        $multiselectOptions = $productId/10 . ',' . ($productId/10+1) . ',' . ($productId/10+2);

        $multiselectSelector = $productSelector . '/multiselect_attribute';
        $multiselectItems = $this->xpath->query($multiselectSelector);
        $multiselectAttributes = $multiselectItems->item(0)->nodeValue;

        $this->assertEquals(
            $multiselectOptions,
            $multiselectAttributes,
            sprintf('Did not have the correct values for multiselect option')
        );
    }

    /**
     * @magentoDataFixture Magento/Framework/Search/_files/product_configurable.php
     * @magentoDbIsolation enabled
     * @magentoAppIsolation enabled
     */
    public function testConfigurableProduct() {

        $this->generateFeed();

        $productSelector = self::PRODUCT_SELECTOR . '[@sku="simple_1020"]';
        $expectedValues = ['sku' => 'simple_1020'];
        $this->assertValues($productSelector, $expectedValues);

        $productSelector = self::PRODUCT_SELECTOR . '[@sku="configurable"]';
        $expectedValues = ['sku' => 'configurable'];
        $this->assertValues($productSelector, $expectedValues);

        $childProductsSelector = $productSelector . '/child_ids/value_1';
        $children = $this->xpath->query($childProductsSelector);
        $this->assertCount(1, $children, 'Did not find only 1 configurable child option');

        $childContentsSelector = $productSelector . '/child_ids/value_1/value_2';
        $childItems = $this->xpath->query($childContentsSelector);
        $this->assertCount(2, $childItems, 'Should be 2 options for child products');

        $childItem1 = $this->xpath->query($childContentsSelector)->item(0)->nodeValue;
        $this->assertEquals("1010", $childItem1, 'Could not find child product');

        $childItem2 = $this->xpath->query($childContentsSelector)->item(1)->nodeValue;
        $this->assertEquals("1020", $childItem2, 'Could not find child product');
    }

    /**
     * @magentoDataFixture Magento/Catalog/_files/product_image.php
     * @magentoDataFixture loadFixtureProductWithImage
     * @magentoDbIsolation enabled
     * @magentoAppIsolation enabled
     */
    public function testHasImage() {

        $this->mutableConfig->setValue('sli_feed_generation/product/cached_image', '1', ScopeInterface::SCOPE_STORE);

        $this->generateFeed();

        $productSelector = self::PRODUCT_SELECTOR . '[@sku="simple"]';
        $expectedValues = [
            'sku' => 'simple',
            'image' => '/m/a/magento_image.jpg'
        ];
        $this->assertValues($productSelector, $expectedValues);

        $cachedImageSelector = self::PRODUCT_SELECTOR . '[@sku="simple"]/sli_cached_image';
        $cachedImageLength = $this->xpath->query($cachedImageSelector)->length;
        $this->assertEquals(1, $cachedImageLength, "Should have cached image generated.");
    }

    /**
     * @magentoDataFixture loadFixtureSimpleProduct
     * @magentoDbIsolation enabled
     * @magentoAppIsolation enabled
     */
    public function testIncludeStockValues() {

        $this->mutableConfig->setValue('sli_feed_generation/general/log_level', 'trace', ScopeInterface::SCOPE_STORE);
        $this->mutableConfig->setValue(
            'sli_feed_generation/product/attributes_select',
            '{"_1543984655894_894":{"attribute_code":"stock_summary"}}',
            ScopeInterface::SCOPE_STORE
        );

        $this->generateFeed();

        $productSelector = self::PRODUCT_SELECTOR . '[@sku="simple"]';
        $expectedValues = [
            'sku' => 'simple',
            'stock_summary/qty' => '22',
            'stock_summary/is_instock' => 'true'
        ];
        $this->assertValues($productSelector, $expectedValues);
    }

    protected function generateFeed() {

        $productEntityCollectionFactory = $this->objectManager->get(
            '\Magento\Catalog\Model\ResourceModel\Product\CollectionFactory'
        );
        $productEntityCollection = $productEntityCollectionFactory->create();

        $categoryEntityCollectionFactory = $this->objectManager->get(
            '\Magento\Catalog\Model\ResourceModel\Category\CollectionFactory'
        );
        $categoryEntityCollection = $categoryEntityCollectionFactory->create();

        $attributeEntityCollectionFactory = $this->objectManager->get(
            '\Magento\Catalog\Model\ResourceModel\Product\Attribute\CollectionFactory'
        );
        $attributeEntityCollection = $attributeEntityCollectionFactory->create();

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

        $categoryGenerator = $this->objectManager->get('\SLI\Feed\Model\Generators\CategoryGenerator');
        $categoryGenerator->writeCollection($categoryEntityCollection, self::STORE_ID, $xmlWriter, $logger);

        $attributeGenerator = $this->objectManager->get('\SLI\Feed\Model\Generators\AttributeGenerator');
        $attributeGenerator->writeCollection($attributeEntityCollection, self::STORE_ID, $logger);

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
            $this->assertCount(1, $nodeList, sprintf('Could not find a node for "%s"', $selector));
            $this->assertEquals(
                $expectedValue,
                $nodeList->item(0)->nodeValue,
                sprintf('Could not find "%s" => "%s"', $selector, $expectedValue)
            );
        }
    }

}