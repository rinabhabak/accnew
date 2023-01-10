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

namespace SLI\Feed\Model\Generators;

use Magento\Catalog\Model\AbstractModel;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\ResourceModel;
use Magento\Catalog\Model\ResourceModel\Product\Collection as ProductCollection;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory as ProductCollectionFactory;
use Magento\CatalogRule\Model\ResourceModel\Rule;
use Magento\CatalogRule\Model\ResourceModel\RuleFactory;
use Magento\Framework\Api\AttributeValueFactory;
use Magento\Framework\Api\ExtensionAttributesFactory;
use Magento\Framework\Model\Context;
use Magento\Framework\Registry;
use Magento\CatalogInventory\Helper\Stock;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\DB\Select;
use Psr\Log\LoggerInterface;
use SLI\Feed\Model\Generators\Helpers\GroupMapLoader;
use SLI\Feed\Helper\XmlWriter;
use SLI\Feed\Helper\GeneratorHelper;

class PriceGenerator extends AbstractModel implements GeneratorInterface
{
    const PRODUCT_PAGE_SIZE = 1000;

    /**
     * Feed generation helper
     *
     * @var GeneratorHelper
     */
    protected $generatorHelper;

    /**
     * Product collection factory
     *
     * @var ProductCollectionFactory
     */
    protected $productCollectionFactory;

    /**
     * Map of the customer group
     * id => name
     *
     * @var array
     */
    protected $groupMap;

    /**
     * Obtains customer groups from Magento.
     *
     * @var GroupMapLoader
     */
    protected $groupMapLoader;

    /**
     * Rule model calculates the product price including catalog price rule.
     *
     * @var RuleFactory
     */
    protected $catalogPriceRuleFactory;

    /**
     * Stock helper
     *
     * @var Stock
     */
    protected $stockHelper;

    /**
     * Generate product info
     *
     * @param Context $context
     * @param Registry $registry
     * @param ExtensionAttributesFactory $extensionFactory
     * @param AttributeValueFactory $customAttributeFactory
     * @param StoreManagerInterface $storeManager
     * @param ResourceModel\Product $resource
     * @param ProductCollection $resourceCollection
     * @param GeneratorHelper $generatorHelper
     * @param ProductCollectionFactory $productCollectionFactory
     * @param GroupMapLoader $groupMapLoader
     * @param RuleFactory $ruleFactory
     * @param Stock $stockHelper
     */
    public function __construct(
        Context $context,
        Registry $registry,
        ExtensionAttributesFactory $extensionFactory,
        AttributeValueFactory $customAttributeFactory,
        StoreManagerInterface $storeManager,
        ResourceModel\Product $resource,
        ProductCollection $resourceCollection,
        GeneratorHelper $generatorHelper,
        ProductCollectionFactory $productCollectionFactory,
        GroupMapLoader $groupMapLoader,
        RuleFactory $ruleFactory,
        Stock $stockHelper
    ) {
        parent::__construct(
            $context,
            $registry,
            $extensionFactory,
            $customAttributeFactory,
            $storeManager,
            $resource,
            $resourceCollection,
            []
        );

        $this->generatorHelper = $generatorHelper;
        $this->productCollectionFactory = $productCollectionFactory;
        $this->groupMapLoader = $groupMapLoader;
        $this->catalogPriceRuleFactory = $ruleFactory;
        $this->stockHelper = $stockHelper;
    }

    /**
     * @param int $storeId
     * @param XmlWriter $xmlWriter
     * @param LoggerInterface $logger
     * @return bool
     */
    public function generateForStoreId($storeId, XmlWriter $xmlWriter, LoggerInterface $logger)
    {
        $logger->debug(sprintf('[%s] starting advanced pricing generator', $storeId));

        if(!$this->generatorHelper->isPriceFeedEnabled($storeId)) {
            $logger->debug(sprintf('[%s] Price XML generation disabled', $storeId));
            return true;
        }

        $logger->debug(sprintf('[%s] Starting price XML generation', $storeId));

        $this->addPricesToFeed($storeId, $xmlWriter, $logger);

        $logger->debug(sprintf('[%s] Finished writing pricing', $storeId));

        return true;
    }

    private function addPricesToFeed($storeId, XMLWriter $xmlWriter, LoggerInterface $logger)
    {
        $logger->debug("Adding Advanced Pricing");

        $entityCollection = $this->productCollectionFactory->create();
        $productCollection = $this->createCollection($entityCollection, $storeId, $logger);
        $rule = $this->catalogPriceRuleFactory->create();
        $this->writeCollection($productCollection, $storeId, $xmlWriter, $logger, $rule);

        $logger->debug(sprintf('[%s] Finished advanced pricing generator', $storeId));
    }

    private function createCollection($entityCollection, $storeId, LoggerInterface $logger)
    {
        // TODO we should recycle the product collection from ProductGenerator.
        // Maybe create a ProductCollectionFactory that internally caches the collection?
        $entityCollection
            ->setStoreId($storeId)
            ->addStoreFilter($storeId)// Need to add to reduce the products that can show for the store
            ->addCategoryIds()
            ->setOrder('entity_id', Select::SQL_ASC);

        if (!$this->generatorHelper->isIncludeOutOfStock($storeId)) {
            $logger->debug(sprintf('[%s] Exclude out of stock items', $storeId));
            $this->stockHelper->addIsInStockFilterToCollection($entityCollection);
        } else {
            $logger->debug(sprintf('[%s] Include out of stock items', $storeId));
        }

        // These need to be done later on as they actually call the SQL so it must go after the SQL
        $entityCollection = $entityCollection
            ->addPriceData()
            ->addUrlRewrite();

        $logger->debug(sprintf("[%s] Product collection select: %s ", $storeId, $entityCollection->getSelectSql(true)));

        return $entityCollection;
    }

    /**
     * Write Collection
     *
     * @param ProductCollection $productCollection
     * @param int $storeId
     * @param XmlWriter $xmlWriter
     * @param LoggerInterface $logger
     * @param Rule $rule
     * @return void
     */
    public function writeCollection(
        ProductCollection $productCollection,
        $storeId,
        XMLWriter $xmlWriter,
        LoggerInterface $logger,
        Rule $rule
    )
    {
        $page = 0;
        $processed = 0; // Counts all products we iterate.
        $processedWithAdvancedPricing = 0; // Counts those products affected by advanced pricing
        $pageSize = self::PRODUCT_PAGE_SIZE;
        $uniqueIdMap = [];

        $this->groupMap = $this->groupMapLoader->load();
        $logger->debug(sprintf('Customer groups [%s]', implode("|", $this->groupMap)));

        $xmlWriter->startElement('advanced_pricing');

        $productCollection->setPage(++$page, $pageSize);
        $productCollection->clear();

        // Loop over each page of the collections
        while ($products = $productCollection->getItems()) {
            $logger->debug("Writing product price data...");

            /** @var $product Product */
            foreach ($products as $product) {
                // depending on the requested data some products might be coming back more than once
                if (array_key_exists($product->getId(), $uniqueIdMap)) {
                    continue;
                }
                $uniqueIdMap[$product->getId()] = $product->getId();

                $hadAdvancedPricing = $this->writeProductPriceData($storeId, $product, $xmlWriter, $rule);
                ++$processed;

                if($hadAdvancedPricing) {
                    $processedWithAdvancedPricing++;
                }

            }
            $logger->debug("Finished processing page: $page");

            //Break out when the number of products is less than the pagesize
            if (count($products) < $pageSize) {
                break;
            }
            $productCollection->setPage(++$page, $pageSize);
            $productCollection->clear();
        }

        // advanced_pricing
        $xmlWriter->endElement();

        $logger->debug("Finished adding prices for $processed products");
        $logger->debug("There were $processedWithAdvancedPricing products with advanced pricing.");
    }

    /**
     * Write the individual product price to the price feed.
     *
     * @param $storeId
     * @param Product $product
     * @param XMLWriter $xmlWriter
     * @param Rule $rule
     * @return bool if there was advanced pricing to add to the feed.
     */
    protected function writeProductPriceData(
        $storeId,
        Product $product,
        XMLWriter $xmlWriter,
        Rule $rule
    ) {
        $catalogPriceData = $this->getCatalogPriceRulesData($storeId, $product, $rule);
        $tierPrice = $this->getPriceByType($product, 'tier_price');

        $hasAdvancedPricing = $this->hasAdvancedPricing($catalogPriceData, $tierPrice);
        if ($hasAdvancedPricing) {
            $xmlWriter->startElement('product_pricing');
            $xmlWriter->writeAttribute('id', $product->getId());
            $xmlWriter->writeNode('id', $product->getId());
            $this->writePriceData('catalog_price_rules', $catalogPriceData, $xmlWriter);
            $this->writePriceData('tier_prices', $tierPrice, $xmlWriter);

            // product
            $xmlWriter->endElement();
        }

        return $hasAdvancedPricing;
    }

    /**
     * @param Product $product
     * @param $priceString string to be found in the product getData call.
     *
     * Magento 2 has merged tiered and grouped prices, so price string of tier_price is currently the only good argument.
     * Using the same code as LSCm1, so we retain the same xml data structure.
     *
     * @return array of tiered and grouped prices.
     */
    protected function getPriceByType(Product $product, $priceString)
    {

        $prices = $product->getData($priceString);
        if (null === $prices) {
            // Live product load - possible performance implication.
            $attribute = $product->getResource()->getAttribute($priceString);
            if ($attribute) {
                $attribute->getBackend()->afterLoad($product);
                $prices = $product->getData($priceString);
            }
        }

        // Return empty array if there are no prices.
        if (!$prices || !is_array($prices)) {
            return array();
        }

        foreach ($prices as &$price) { // & means Array passed by reference to allow editing.
            foreach ($price as $key => $value) {
                // Add customer group name to node.
                if (strcmp($key, 'cust_group') === 0) {
                    if (isset($this->groupMap[$value])) {
                        $price['cust_name'] = $this->groupMap[$value];
                    }
                }
            }
        }

        return $prices;
    }


    /**
     * Gets the Catalog Price Rules (CPR) for a product
     *
     * @param $storeId
     * @param Product $product
     * @param Rule $rule
     * @return array catalog price rule data about the product if it's effected by a rule.
     */
    private function getCatalogPriceRulesData($storeId, Product $product, Rule $rule)
    {
        $prices = array();

        foreach ($this->groupMap as $customerGroupId => $name) {

            $finalPrice = $rule->getRulePrice(new \DateTime(), $storeId, $customerGroupId, $product->getId());

            if (!empty($finalPrice)) {
                $priceInfo = array();
                $priceInfo['final_price'] = $finalPrice;
                $priceInfo['customer_name'] = $name;
                $priceInfo['price'] = $product->getPrice();
                $prices[] = $priceInfo;
            }
        }

        return $prices;
    }

    /**
     * Were any of arrays set, and thus there is advanced pricing to apply in the export.
     *
     * @param array $catalogPriceData
     * @param array $tierPrice
     *
     * @return bool if any of the arrays are set
     */
    protected function hasAdvancedPricing($catalogPriceData, $tierPrice)
    {
        return count($catalogPriceData) > 0 || count($tierPrice) > 0;
    }

    /**
     * @param string $priceType name of the advanced pricing node eg 'group_prices'
     * @param array $priceData the actual data to output. Must be of format:
     *                                               array (
     *                                               array ( key => value ),
     *                                               ...
     *                                               array ( key => value )
     *                                               )
     * @param XMLWriter $xmlWriter
     */
    protected function writePriceData($priceType, $priceData, $xmlWriter)
    {
        if (!$priceData || !is_array($priceData)) {
            return;
        }

        $xmlWriter->startElement($priceType);
        foreach ($priceData as $price) {
            $xmlWriter->startElement("price");
            foreach ($price as $key => $value) {
                $xmlWriter->writeNode($key, $value);
            }
            $xmlWriter->endElement();
        }
        $xmlWriter->endElement();
    }
}