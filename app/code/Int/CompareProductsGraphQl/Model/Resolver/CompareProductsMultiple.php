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

class CompareProductsMultiple implements ResolverInterface
{
    /**
     * @var \Magento\Catalog\Api\ProductRepositoryInterface
     */
    private $_productRepository;

    /**
     * @var \Magento\Catalog\Model\Product\Compare\ListCompare
     */
    protected $_productCompareList;
    protected $compareFactory;
    protected $compareItem;
    protected $_catalogProductVisibility;
    protected $_catalogConfig;
    protected $_items;
    protected $_finalItems;

    public function __construct(
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepositoryInterface,
        \Magento\Catalog\Model\Product\Compare\ListCompare $catalogProductCompareList,
        \Magento\Reports\Model\Product\Index\ComparedFactory $compareFactory,
        \Magento\Catalog\Model\Product\Compare\Item $compareItem,
        \Magento\Catalog\Model\Product\Visibility $catalogProductVisibility,
        \Magento\Catalog\Model\Config $catalogConfig,
        \Magento\Catalog\Model\ResourceModel\Product\Compare\Item\CollectionFactory $itemCollectionFactory
    )
    {
        $this->_productRepository = $productRepositoryInterface;
        $this->_productCompareList = $catalogProductCompareList;
        $this->compareFactory = $compareFactory;
        $this->compareItem = $compareItem;
        $this->_catalogProductVisibility = $catalogProductVisibility;
        $this->_catalogConfig = $catalogConfig;
        $this->_itemCollectionFactory = $itemCollectionFactory;
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
            $storeId = (int) $context->getExtensionAttributes()->getStore()->getId();
            $product_ids = $args['product_id']; 
            $customer_id = $context->getUserId();
            $compareProducts = [];
         
            if ($customer_id) {
            $this->compareItem->setCustomerId($customer_id);
            }
            
            /* delete all items start */
            $this->_items = $this->_itemCollectionFactory->create();
            $this->_items->useProductItem(true)->setStoreId($storeId);
            if ($customer_id) {
                $this->_items->setCustomerId($customer_id);
            }
            $this->_items->addAttributeToSelect(
                $this->_catalogConfig->getProductAttributes()
            )->loadComparableAttributes()->addMinimalPrice()->addTaxPercents()
            ->setVisibility(
                $this->_catalogProductVisibility->getVisibleInSiteIds()
            );
            
            if($this->_items->getSize() > 0){
                $this->_items->clear();
            }
            /* delete all items end.*/
            if(count($product_ids) >0)
            {
                $product_ids = array_unique($product_ids);
               foreach($product_ids as $productId)
                {
                    $this->compareItem->setCustomerId($customer_id);
                    $this->compareItem->addProductData($productId);
                    $this->compareItem->save();
                    $this->compareItem->unsetData();
                }
               
                $this->_finalItems = $this->_itemCollectionFactory->create();
            $this->_finalItems->useProductItem(true)->setStoreId($storeId);
            if ($customer_id) {
                $this->_finalItems->setCustomerId($customer_id);
            }
            $this->_finalItems->addAttributeToSelect(
                $this->_catalogConfig->getProductAttributes()
            )->loadComparableAttributes()->addMinimalPrice()->addTaxPercents()
            ->setVisibility(
                $this->_catalogProductVisibility->getVisibleInSiteIds()
            );

                if($this->_finalItems->getSize() > 0){
                foreach($this->_finalItems as $item){
                    if(is_array($item->getAttributeText('mounting'))){
                        $mounting = implode(',',$item->getAttributeText('mounting'));
                    }
                else{
                    $mounting = $item->getAttributeText('mounting');
                }
                $compareProducts[] = [
                    'product_name' => $item->getName(),
                    'product_sku' => $item->getSku(),
                    'product_id' => $item->getEntityId(),
                    'product_price' => $item->getFinalPrice(),
                    'product_image' => $item->getImage(),
                    'description' => $item->getDescription(),
                    'extension' => $item->getAttributeText('extension'),
                    'specifications' => $item->getSpecifications(),
                    'load_rating' => $item->getAttributeText('load_rating'),
                    'material' => $item->getAttributeText('material'),
                    'disconnect' => $item->getAttributeText('disconnect'),
                    'category' => $item->getAttributeText('custom_category'),
                    'side_space' => $item->getAttributeText('side_space'),
                    'mounting' => $mounting

                ];
            }

        }

        return $compareProducts;
            }
            else{
                return [
                "message" => __('You cleared the comparison list.')
            ];
            }

            

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
    private function getProductId($productId): int
    {
        if (!isset($productId)) {
            throw new GraphQlInputException(
                __('Product id should be specified')
            );
        }
        $args = [];
        return $args['product_id'];
    }
}