<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Int\ConfiguratorQuoteGraphQl\Model\Resolver;

use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Int\ConfiguratorQuoteGraphQl\Model\Cart\AddProductsToCart;
use Magento\QuoteGraphQl\Model\Cart\GetCartForUser;

/**
 * Add configurator BOM products to cart GraphQl resolver
 * {@inheritdoc}
 */
class AddConfiguratorProductsToCart implements ResolverInterface
{
    /**
     * @var GetCartForUser
     */
    private $getCartForUser;

    /**
     * @var AddProductsToCart
     */
    private $addProductsToCart;

    /**
     * @param GetCartForUser $getCartForUser
     * @param AddProductsToCart $addProductsToCart
     */
    public function __construct(
        GetCartForUser $getCartForUser,
        AddProductsToCart $addProductsToCart
    ) {
        $this->getCartForUser = $getCartForUser;
        $this->addProductsToCart = $addProductsToCart;
    }

    /**
     * @inheritdoc
     */
    public function resolve(Field $field, $context, ResolveInfo $info, array $value = null, array $args = null)
    {
        if (empty($args['input']['cart_id'])) {
            throw new GraphQlInputException(__('Required parameter "cart_id" is missing'));
        }
        $maskedCartId = $args['input']['cart_id'];

        if (empty($args['input']['cart_items'])
            || !is_array($args['input']['cart_items'])
        ) {
            throw new GraphQlInputException(__('Required parameter "cart_items" is missing'));
        }
        
        $cartItems = $args['input']['cart_items'];
        

        $storeId = (int)$context->getExtensionAttributes()->getStore()->getId();
        $cart = $this->getCartForUser->execute($maskedCartId, $context->getUserId(), $storeId);
        
        
        $this->addProductsToCart->execute($cart, $cartItems);
        
        
        if(isset($args['input']['configurator_pid'])){
            $items = $cart->getAllVisibleItems();
            $i = 0;
            foreach($items as $item) {
                $_item = $this->searchInArray($item->getSku(),'sku',$cartItems);
                if(null !== $_item){
                    $projectId = $args['input']['configurator_pid'];
                    $i++;
                    $itemProjectId = $item->getConfiguratorPid();
                    if($itemProjectId){
                        $itemProjectIds = explode(',',$itemProjectId);                    
                        array_push($itemProjectIds, $projectId);
                        
                        $itemProjectIds = array_unique($itemProjectIds);
                        $itemProjectIds = implode(",",$itemProjectIds);
                        $item->setConfiguratorPid($itemProjectIds)->save();
                        
                    }else{
                        $item->setConfiguratorPid($projectId)->save();
                    }
                }
            }
        }
        
        
        // Add project id to quote
        if(isset($args['input']['configurator_pid'])){
            $projectId = $args['input']['configurator_pid'];
            
            if($cart->getConfiguratorPid()){
                $cartProjectId = $cart->getConfiguratorPid();
                $cartProjectIds = explode(',',$cartProjectId);
                array_push($cartProjectIds, $projectId);
                $cartProjectIds = array_unique($cartProjectIds);
                //$cartProjectIds = implode(",",$cartProjectIds);
                
                $items = $cart->getAllVisibleItems();
                $_itemPids = array();
                foreach($items as $item){
                    $_itemPid = $item->getConfiguratorPid();
                    if($_itemPid){
                        $itemPids = explode(',', $_itemPid);
                        $_itemPids = array_merge($_itemPids, $itemPids);
                    }
                }
                
                
                $_itemPids = array_unique($_itemPids);
                
                $_cartProjectId = array_intersect($cartProjectIds, $_itemPids);
                $_cartProjectId = implode(",",$_cartProjectId);
                $cart->setConfiguratorPid($_cartProjectId)->save();
                
            }else{
                $cart->setConfiguratorPid($projectId)->save();
            }
        }

        return [
            'cart' => [
                'model' => $cart,
            ],
        ];
    }
    
    
    
    function searchInArray($sku, $search_key, $array) {
       
        foreach ($array as $key => $val) {
            $data = $val['data'];
            if ($data[$search_key] === $sku) {
                return $key;
            }
        }
       return null;
    }
}
