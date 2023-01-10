<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_ShopbyBrandGraphQl
 */


declare(strict_types=1);

namespace Amasty\ShopbyBrandGraphQl\Model\Resolver;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\GraphQl\Exception\GraphQlNoSuchEntityException;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\ResolverInterface;

class MoreFromThisBrandBlock implements ResolverInterface
{
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var \Magento\Framework\Registry
     */
    private $registry;

    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @var \Amasty\ShopbyBrand\Block\Catalog\Product\ProductList\MoreFrom
     */
    private $moreFrom;

    public function __construct(
        \Amasty\ShopbyBrand\Block\Catalog\Product\ProductList\MoreFrom $moreFrom,
        \Magento\Framework\Registry $registry,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        ProductRepositoryInterface $productRepository
    ) {
        $this->storeManager = $storeManager;
        $this->registry = $registry;
        $this->productRepository = $productRepository;
        $this->moreFrom = $moreFrom;
    }

    /**
     * @param Field $field
     * @param \Magento\Framework\GraphQl\Query\Resolver\ContextInterface $context
     * @param ResolveInfo $info
     * @param array|null $value
     * @param array|null $args
     * @return array|\Magento\Framework\GraphQl\Query\Resolver\Value|mixed
     * @throws \Exception
     */
    public function resolve(
        Field $field,
        $context,
        ResolveInfo $info,
        array $value = null,
        array $args = null
    ) {
        try {
            $this->setData($args);
            return $this->getData();
        } catch (\Exception $e) {
            throw new GraphQlNoSuchEntityException(__('Wrong parameter storeId.'));
        }
    }

    /**
     * @param array $args
     * @throws NoSuchEntityException
     */
    private function setData(array $args = [])
    {
        if (isset($args['storeId'])) {
            $this->storeManager->setCurrentStore($args['storeId']);
        }
        $product = $this->getProductById($args['productId']);
        if (isset($args['storeId'])) {
            $this->registry->register('product', $product);
        }
    }

    /**
     * @return array
     */
    public function getData()
    {
        $data['title'] = $this->moreFrom->getTitle();
        $items = $this->moreFrom->getItemCollection();
        foreach ($items as $key => $item) {
            $data['items'][$key] = $item->getData();
            $data['items'][$key]['model'] = $item;
        }

        return $data;
    }

    /**
     * @param $productId
     * @return ProductInterface|null
     */
    private function getProductById($productId)
    {
        try {
            return $this->productRepository->getById($productId, false);
        } catch (NoSuchEntityException $e) {
            return null;
        }
    }
}
