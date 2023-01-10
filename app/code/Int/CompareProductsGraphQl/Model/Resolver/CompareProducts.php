<?php
/**
 * Copyright © Int, Inc. All rights reserved.
 */
declare(strict_types=1);

namespace Int\CompareProductsGraphQl\Model\Resolver;

use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Exception\GraphQlNoSuchEntityException;
use Magento\Framework\GraphQl\Exception\GraphQlAuthorizationException;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Framework\GraphQl\Query\Resolver\ContextInterface;
use Magento\Framework\GraphQl\Query\Resolver\Value;
use Magento\Framework\GraphQl\Query\Resolver\ValueFactory;
use Magento\Authorization\Model\UserContextInterface;

class CompareProducts implements ResolverInterface
{
    /**
     * @var \Magento\Catalog\Api\ProductRepositoryInterface
     */
    private $_productRepository;

    /**
     * @var \Magento\Catalog\Model\Product\Compare\ListCompare
     */
    private $compareItem;
    private $compareFactory;
    protected $_items;
    protected $_catalogConfig;
    protected $_catalogProductVisibility;

    public function __construct(
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepositoryInterface,
        \Magento\Catalog\Model\Product\Compare\Item $compareItem,
        \Magento\Reports\Model\Product\Index\ComparedFactory $compareFactory,
        \Magento\Catalog\Model\ResourceModel\Product\Compare\Item\CollectionFactory $itemCollectionFactory,
        \Magento\Catalog\Model\Config $catalogConfig,
        \Magento\Catalog\Model\Product\Visibility $catalogProductVisibility
    ){
        $this->_productRepository = $productRepositoryInterface;
        $this->compareItem = $compareItem;
        $this->compareFactory = $compareFactory;
        $this->_itemCollectionFactory = $itemCollectionFactory;
        $this->_catalogConfig = $catalogConfig;
        $this->_catalogProductVisibility = $catalogProductVisibility;
    }

    /**
     * @inheritdoc
     */
    public function resolve(
        Field $field,
        $context,
        ResolveInfo $info,
        array $value = null,
        array $args = null
    ) {

        if (false === $context->getExtensionAttributes()->getIsCustomer()) {
            throw new GraphQlAuthorizationException(
                __('The current customer isn\'t authorized.')
            );
        }

        try{
            $product_id = $this->getProductId($args);
            $storeId = (int) $context->getExtensionAttributes()->getStore()->getId();
            $customerId = $context->getUserId();
            /* check product is added start */
            $this->_items = $this->_itemCollectionFactory->create();
            $this->_items->useProductItem(true)->setStoreId($storeId);
            if ($customerId) {
                $this->_items->setCustomerId($customerId);
            }
            $this->_items->addAttributeToSelect(
                $this->_catalogConfig->getProductAttributes()
            )->loadComparableAttributes()->addMinimalPrice()->addTaxPercents()
            ->setVisibility(
                $this->_catalogProductVisibility->getVisibleInSiteIds()
            );
            $product = $this->_productRepository->getById($product_id, false, $storeId);
             if($this->_items->getSize() > 0){
                foreach($this->_items as $item){
                    if($item->getEntityId() == $product_id)
                        return [
                            "message" => __('You added product %1 to the comparison list.', $product->getName())
                        ];
                }
            }


            /* check product is added end */
            $this->compareItem->setCustomerId($customerId);
            $this->compareItem->addProductData($product_id);
            $this->compareItem->save();

            return [
                "message" => __('You added product %1 to the comparison list.', $product->getName())
            ];

        } catch (\Exception $e) {
            throw new GraphQlInputException(
                __($e->getMessage())
            );
        }
        
    }

    /**
     * @param array $args
     * @return int
     * @throws GraphQlInputException
     */
    private function getProductId(array $args): int
    {
        if (!isset($args['product_id'])) {
            throw new GraphQlInputException(
                __('Product id should be specified')
            );
        }

        return (int)$args['product_id'];
    }
}
