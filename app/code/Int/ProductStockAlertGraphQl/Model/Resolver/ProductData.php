<?php

namespace Int\ProductStockAlertGraphQl\Model\Resolver;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlAuthorizationException;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Exception\GraphQlNoSuchEntityException;
use Magento\Framework\GraphQl\Query\Resolver\Value;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;

class ProductData extends \Bss\ProductStockAlertGraphQl\Model\Resolver\ProductData
{
    /**
     * @param Field $field
     * @param \Magento\Framework\GraphQl\Query\Resolver\ContextInterface $context
     * @param ResolveInfo $info
     * @param array|null $value
     * @param array|null $args
     * @return Value
     * @throws GraphQlAuthorizationException
     * @throws GraphQlInputException
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function resolve(
        Field $field,
              $context,
        ResolveInfo $info,
        array $value = null,
        array $args = null
    ): Value {
        $currentUserId = $context->getUserId();

        if (!isset($args['product_id'])) {
            throw new GraphQlInputException(__('Product ID is required'));
        }
        if (!isset($args['website_id'])) {
            throw new GraphQlInputException(__('Website ID is required'));
        }
        $productId = $args['product_id'];
        $websiteId = $args['website_id'];

        $productData = $this->getProductData($productId, $websiteId, $currentUserId);

        return $this->valueFactory->create(
            function () use ($productData) {
                if (is_array($productData) &&
                    !isset($productData['product_stock_alert'])) {
                    return ['product_data' => $productData];
                }
                return ['product_data' => [$productData]];
            }
        );
    }

    /**
     * @param int $productId
     * @param int $websiteId
     * @param int $customerId
     * @return array
     * @throws LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getProductData($productId, $websiteId, $customerId): array
    {
        try {
            $product = $this->productRepository->getById($productId);
            $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
            $customer = $objectManager->create('\Magento\Customer\Model\Customer')->load($customerId);

            $website = $this->storeManager->getWebsite($websiteId);
            $stockResolver = $this->msiHelper->getStockResolverObject();
            $salableQty = $this->msiHelper->getSalableQtyObject();
            $stockId = $this->getStockId($website->getId(), $stockResolver, $salableQty);
            $productType = $product->getTypeId();
            $result = [];

            if ($this->checkProductType($productType, 'simple')) {
                $result = $this->buildSimple($product, $customer, $stockId, $website->getId(), $salableQty);
            } elseif ($this->checkProductType($productType, 'configurable')) {
                $result = $this->buildConfigurable($product, $customer, $stockId, $website->getId(), $salableQty);
            } elseif ($this->checkProductType($productType, 'grouped')) {
                $result = $this->buildGrouped($product, $customer, $stockId, $website->getId(), $salableQty);
            } elseif ($this->checkProductType($productType, 'bundle')) {
                $result = $this->buildBundle($product, $customer, $stockId, $website->getId(), $salableQty);
            }
            return $result;
        } catch (NoSuchEntityException $exception) {
            throw new GraphQlNoSuchEntityException(__($exception->getMessage()));
        } catch (\Exception $exception) {
            throw new GraphQlNoSuchEntityException(__('We can not find data match with product ID = %1, website ID = %2', $productId, $websiteId));
        }
    }

    protected function isProductEnabledNotice($product): bool
    {
        if ($product->getCustomAttribute('product_stock_alert')) {
            if($product->getCustomAttribute('product_stock_alert')->getValue() == 1){
                return true;
            }
            if($product->getCustomAttribute('product_stock_alert')->getValue() == 2){
                return false;
            }
        }
        return true;
    }
}