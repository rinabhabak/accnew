<?php

namespace Int\ProductStockAlertGraphQl\Model\Resolver;

use Bss\ProductStockAlert\Model\StockFactory;
use Exception;
use Magento\Framework\GraphQl\Query\ResolverInterface;

class UnSubscribe extends \Bss\ProductStockAlertGraphQl\Model\Resolver\UnSubscribe implements ResolverInterface
{
    /**
     * @param int $productId
     * @param int $parentId
     * @param int $websiteId
     * @param int $customerId
     * @param array $resultData
     * @return array
     */
    public function unsubscribeStockNotice(
        $productId,
        $parentId,
        $websiteId,
        $customerId,
        $resultData
    ): array {
        try {
            $product = $this->productRepository->getById($productId);
            $customer = $this->customerRepository->getById($customerId);
            $parent = $this->productRepository->getById($parentId);
            $website = $this->storeManager->getWebsite($websiteId);

            if (!$this->productValidate->validateChildProduct($product, $parent)) {
                $resultData[] = [
                    'message' => __('Product ID %1 is not child of product ID %2', $product->getId(), $parent->getId()),
                    'params' => $this->jsonSerialize->serialize([
                        'PRODUCT' => $product->getId(),
                        'PARENT_ID' => $parent->getId()
                    ])
                ];
            }

            $hasEmail = $this->stockResource->hasEmail(
                $customer->getId(),
                $product->getId(),
                $websiteId
            );

            if (!$hasEmail) {
                $resultData[] = [
                    'message' => __('You did not subscribe on product %1.', $product->getSku()),
                    'params' => $this->jsonSerialize->serialize([
                        'SKU' => $product->getSku(),
                        'WEBSITE' => $website->getCode()
                    ])
                ];
            }

            if (empty($resultData)) {
                $stockModel = $this->stockFactory->create()
                    ->setCustomerId($customer->getId())
                    ->setProductId($product->getId())
                    ->setWebsiteId(
                        $website->getId()
                    )->setStoreId(
                        $product->getStoreId()
                    )->setParentId(
                        $parent->getId()
                    )
                    ->loadByParam();

                if ($stockModel->getAlertStockId()) {
                    $stockModel->delete();
                } else {
                    $resultData[] = [
                        'message' => __('We could not find any record match with product %1', $product->getSku()),
                        'params' => $this->jsonSerialize->serialize([
                            'SKU: ' => $product->getSku(),
                            'WEBSITE' => $website->getCode()
                        ])
                    ];
                }
            }
        } catch (Exception $exception) {
            $resultData[] = [
                'message' => $exception->getMessage(),
                'params' => ''
            ];
        }
        return $resultData;
    }
}