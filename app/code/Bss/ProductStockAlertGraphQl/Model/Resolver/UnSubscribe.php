<?php
/**
 * BSS Commerce Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://bsscommerce.com/Bss-Commerce-License.txt
 *
 * @category   BSS
 * @package    Bss_ProductStockAlertGraphQl
 * @author     Extension Team
 * @copyright  Copyright (c) 2020-2021 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
declare(strict_types=1);

namespace Bss\ProductStockAlertGraphQl\Model\Resolver;

use Bss\ProductStockAlert\Model\ResourceModel\Stock;
use Bss\ProductStockAlert\Model\StockFactory;
use Bss\ProductStockAlertGraphQl\Model\Product\Validate as ProductValidate;
use Exception;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlAuthorizationException;
use Magento\Framework\GraphQl\Query\Resolver\Value;
use Magento\Framework\GraphQl\Query\Resolver\ValueFactory;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\GraphQl\Model\Query\ContextInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\Serialize\Serializer\Json as JsonSerialize;

class UnSubscribe implements ResolverInterface
{
    /**
     * @var StockFactory
     */
    protected $stockFactory;

    /**
     * @var ProductValidate
     */
    protected $productValidate;

    /**
     * @var Stock
     */
    protected $stockResource;

    /**
     * @var CustomerRepositoryInterface
     */
    protected $customerRepository;

    /**
     * @var ProductRepositoryInterface
     */
    protected $productRepository;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var JsonSerialize
     */
    protected $jsonSerialize;

    /**
     * @var ValueFactory
     */
    protected $valueFactory;

    /**
     * UnSubscribe constructor.
     * @param StockFactory $stockFactory
     * @param ProductValidate $productValidate
     * @param Stock $stockResource
     * @param CustomerRepositoryInterface $customerRepository
     * @param ProductRepositoryInterface $productRepository
     * @param StoreManagerInterface $storeManager
     * @param JsonSerialize $jsonSerialize
     * @param ValueFactory $valueFactory
     */
    public function __construct(
        StockFactory $stockFactory,
        ProductValidate $productValidate,
        Stock $stockResource,
        CustomerRepositoryInterface $customerRepository,
        ProductRepositoryInterface $productRepository,
        StoreManagerInterface $storeManager,
        JsonSerialize $jsonSerialize,
        ValueFactory $valueFactory
    ) {
        $this->stockFactory = $stockFactory;
        $this->productValidate = $productValidate;
        $this->stockResource = $stockResource;
        $this->customerRepository = $customerRepository;
        $this->productRepository = $productRepository;
        $this->storeManager = $storeManager;
        $this->jsonSerialize = $jsonSerialize;
        $this->valueFactory = $valueFactory;
    }

    /**
     * @param Field $field
     * @param \Magento\Framework\GraphQl\Query\Resolver\ContextInterface $context
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
        /**
         * @var ContextInterface $context
         */
        if (false === $context->getExtensionAttributes()->getIsCustomer()) {
            throw new GraphQlAuthorizationException(__('Login to access this feature'));
        }
        $this->validate($args, $resultData);
        $productId = $args['product_id'];
        $parentId = $args['parent_id'];
        $websiteId = $args['website_id'];
        $dataRender = $this->unsubscribeStockNotice(
            $productId,
            $parentId,
            $websiteId,
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
     * @param array $args
     * @param array $resultData
     */
    protected function validate(array $args, array &$resultData): void
    {
        if (!isset($args['product_id'])) {
            $resultData[] = [
                'message' => 'Product ID is required',
                'params' => ''
            ];
        }
        if (!isset($args['parent_id'])) {
            $resultData[] = [
                'message' => 'Parent ID is required',
                'params' => ''
            ];
        }
        if (!isset($args['website_id'])) {
            $resultData[] = [
                'message' => 'Website ID is required',
                'params' => ''
            ];
        }
    }

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
                    'message' => 'Product ID %PRODUCT is not child of product ID %PARENT_ID',
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
                    'message' => 'You did not subscribe on product %SKU in website %WEBSITE.',
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
                        'message' => 'We could not find any record match with product %SKU in website %WEBSITE.',
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
