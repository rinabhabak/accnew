<?php

namespace Int\ProductStockAlertGraphQl\Model\Resolver;

use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlAuthorizationException;
use Magento\Framework\GraphQl\Query\Resolver\ContextInterface;
use Magento\Framework\GraphQl\Query\Resolver\Value;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;

class Subscribe extends \Bss\ProductStockAlertGraphQl\Model\Resolver\Subscribe
{
    /**
     * @param Field $field
     * @param ContextInterface $context
     * @param ResolveInfo $info
     * @param array|null $value
     * @param array|null $args
     * @return Value
     * @throws GraphQlAuthorizationException
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function resolve(
        Field $field,
              $context,
        ResolveInfo $info,
        array $value = null,
        array $args = null
    ): Value {
        $resultData = [];
        $currentUserId = $context->getUserId();

        $this->validate($args, $resultData);
        $productId = $args['product_id'];
        $parentId = $args['parent_id'];
        $email = $args['email'];
        $websiteId = $args['website_id'];

        if ($email &&
            strlen($email) > 1 &&
            !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $resultData[] = [
                'message' => 'Please correct the email address',
                'params' => $this->jsonSerialize->serialize([
                    'EMAIL' => $email
                ])
            ];
        }

        $dataRender = $this->subscribeStockNotice(
            $productId,
            $parentId,
            $websiteId,
            $email,
            $currentUserId,
            $resultData
        );
        return $this->valueFactory->create(
            function () use ($dataRender) {
                return $dataRender;
            }
        );
    }

    /**
     * @param $productId
     * @param $parentId
     * @param $websiteId
     * @param $email
     * @param $customerId
     * @param $resultData
     * @return array
     */
    public function subscribeStockNotice(
        $productId,
        $parentId,
        $websiteId,
        $email,
        $customerId,
        $resultData
    ): array {
        try {
            $product = $this->productRepository->getById($productId);
            $customerName = "Guest";
            try {
                $customer = $this->customerRepository->getById($customerId);
                $customerName = $customer->getFirstname() . " " . $customer->getLastname();
            }catch (\Exception $exception){
                // Do nothing
            }

            $parent = $this->productRepository->getById($parentId);
            $website = $this->storeManager->getWebsite($websiteId);

            if (!$email || strlen($email) === 0 || $email === '') {
                $email = $customer->getEmail();
            }


            if (!$this->productValidate->validateChildProduct($product, $parent)) {

                if($parent->getTypeId() == \Magento\GroupedProduct\Model\Product\Type\Grouped::TYPE_CODE &&
                    $product->getTypeId() == \Magento\GroupedProduct\Model\Product\Type\Grouped::TYPE_CODE &&
                    $parent->getId() === $product->getId()
                ){

                }else{
                    $resultData[] = [
                        'message' => __('Product ID %1 is not child of product ID %2',  $product->getId(), $parent->getId()),
                    ];
                }
            }

            $stockResolver = $this->msiHelper->getStockResolverObject();
            $salableQty = $this->msiHelper->getSalableQtyObject();
            $stockId = $this->getStockId($websiteId, $stockResolver, $salableQty);
            $isInStock = $this->isInStock(
                $product->getSku(),
                $product->getIsSalable(),
                $stockId,
                $salableQty
            );
            if ($isInStock || !$this->isProductEnabledNotice($product)) {
                $resultData[] = [
                    'message' => __('Product with sku %1 is not allow to subscribe a stock notice right now.', $product->getSku())
                ];
            }

            $select = $this->stockResource->getConnection()->select()->from(
                $this->stockResource->getMainTable()
            )->where(
                'customer_email = :customer_email'
            )->where(
                'product_id  = :product_id'
            )->where(
                'website_id  = :website_id'
            );
            $bind = [
                ':customer_email' => $email,
                ':product_id' => $productId,
                ':website_id' => $websiteId,
            ];

            $data = $this->stockResource->getConnection()->fetchRow($select, $bind);
            $hasEmail = is_array($data) && isset($data['alert_stock_id']) && count($data);


            if ($hasEmail) {
                $resultData[] = [
                    'message' => __('You already subscribed for product %1.', $product->getSku())
                ];
            }

            if (empty($resultData)) {
                $model = $this->stockFactory->create()
                    ->setCustomerId($customerId)
                    ->setCustomerEmail($email)
                    ->setCustomerName($customerName)
                    ->setProductSku($product->getSku())
                    ->setProductId($product->getId())
                    ->setWebsiteId(
                        $websiteId
                    )
                    ->setStoreId(
                        $website->getDefaultStore()->getId()
                    )
                    ->setParentId($parent->getId());
                $model->save();
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