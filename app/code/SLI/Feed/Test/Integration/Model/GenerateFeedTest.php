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
use Magento\Framework\ObjectManagerInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\TestFramework\Helper\Bootstrap;
use SLI\Feed\Console\Command\ProcessFeedCommand;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;

/**
 * Class GenerateFeedTest
 * @package SLI\Feed\Test\Integration\Model
 *
 * Reference pages C:/magento_source/archive/magento2/dev/tests/integration/testsuite/Magento/Catalog/Console/Command/ProductAttributesCleanUpTest.php
 * http://devdocs.magento.com/guides/v2.1/test/integration/integration_test_execution_cli.html
 */
class GenerateFeedTest extends \PHPUnit\Framework\TestCase
{

    /**
     * @var ProcessFeedCommand
     */
    protected $command;

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

    public function setUp()
    {

        $this->objectManager = Bootstrap::getObjectManager();
        $processFeedCommand = $this->objectManager->get('\SLI\Feed\Console\Command\ProcessFeedCommand');

        $application = new Application();
        $application->add($processFeedCommand);
        $this->command = $application->find('sli:feed:process');
        $this->command->addOption('skip-ftp');
        $this->command->addOption('force');

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
     * @magentoDbIsolation enabled
     * @magentoAppIsolation enabled
     */
    public function testFeedGeneration()
    {
        $this->generateFeed();

        $productFeed = $this->xpath->query('//products')->length;
        $attributeFeed = $this->xpath->query('//attributes')->length;
        $categoryFeed = $this->xpath->query('//categories')->length;
        $priceFeed = $this->xpath->query('//advanced_pricing')->length;

        $this->assertGreaterThanOrEqual(1, $productFeed);
        $this->assertGreaterThanOrEqual(1, $attributeFeed);
        $this->assertGreaterThanOrEqual(1, $categoryFeed);
        $this->assertEquals(0, $priceFeed);
    }

    /**
     * @magentoDbIsolation enabled
     * @magentoAppIsolation enabled
     */
    public function testAdvancedPricing()
    {

        $this->mutableConfig->setValue('sli_feed_generation/feed/advanced_pricing', '1', ScopeInterface::SCOPE_STORE);

        $this->generateFeed();

        $productFeed = $this->xpath->query('//products')->length;
        $attributeFeed = $this->xpath->query('//attributes')->length;
        $categoryFeed = $this->xpath->query('//categories')->length;
        $priceFeed = $this->xpath->query('//advanced_pricing')->length;

        $this->assertGreaterThanOrEqual(1, $productFeed);
        $this->assertGreaterThanOrEqual(1, $attributeFeed);
        $this->assertGreaterThanOrEqual(1, $categoryFeed);
        $this->assertGreaterThanOrEqual(1, $priceFeed);
    }

    public function generateFeed()
    {
        $tester = new CommandTester($this->command);

        // Generate feeds
        $tester->execute([]);

        $generatorHelper = $this->objectManager->get('\SLI\Feed\Helper\GeneratorHelper');

        $feedFilename = sprintf($generatorHelper->getFeedFileTemplate(), '1');
        $xml = new DomDocument;
        $xml->load($feedFilename);
        $this->xpath = new DOMXPath($xml);
    }
}