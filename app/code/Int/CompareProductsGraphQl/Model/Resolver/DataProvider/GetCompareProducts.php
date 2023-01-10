<?php
/**
 * Copyright © Int, Inc. All rights reserved.
 */
declare(strict_types=1);

namespace Int\CompareProductsGraphQl\Model\Resolver\DataProvider;

use Magento\Customer\Model\Context;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;

class GetCompareProducts
{
    protected $_items;
    protected $_itemCollectionFactory;
    protected $_compareProduct;
    protected $_catalogConfig;
    protected $_catalogProductVisibility;
    protected $_listCompare;

    public function __construct(
        \Magento\Catalog\Helper\Product\Compare $compareProduct,
        \Magento\Catalog\Model\Config $catalogConfig,
        \Magento\Catalog\Model\Product\Visibility $catalogProductVisibility,
        \Magento\Catalog\Block\Product\Compare\ListCompare $listCompare,
        \Magento\Catalog\Model\ResourceModel\Product\Compare\Item\CollectionFactory $itemCollectionFactory,
        \Magento\Catalog\Model\Product\Compare\ListCompare $listCompareModel
    )
    {
        $this->_listCompare = $listCompare;
        $this->_catalogConfig = $catalogConfig;
        $this->_compareProduct = $compareProduct;
        $this->_catalogProductVisibility = $catalogProductVisibility;
        $this->_itemCollectionFactory = $itemCollectionFactory;
        $this->listCompare = $listCompareModel;
    }

    /**
     * Retrieve Product Compare items collection
     */
    public function getItems(int $customer_id, int $store_id) : array
    {
        try{
        $compareProducts = [];
        
        if ($this->_items === null) {
            $this->_compareProduct->setAllowUsedFlat(false);

            $this->_items = $this->_itemCollectionFactory->create();
            $this->_items->useProductItem(true)->setStoreId($store_id);

           
              
            if ($customer_id) {
                $this->_items->setCustomerId($customer_id);
            }
             
            $this->_items->addAttributeToSelect(
                $this->_catalogConfig->getProductAttributes()
            )->loadComparableAttributes()->addMinimalPrice()->addTaxPercents()
            ->setVisibility(
                $this->_catalogProductVisibility->getVisibleInSiteIds()
            );
        }

        if($this->_items->getSize() > 0){
            foreach($this->_items as $item){
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
    }
        catch (\Exception $e) {
            throw new GraphQlInputException(
                __($e->getMessage())
            );
        }

        return $compareProducts;
    }
}