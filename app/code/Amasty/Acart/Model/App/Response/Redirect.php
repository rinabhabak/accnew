<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Acart
 */


namespace Amasty\Acart\Model\App\Response;

class Redirect extends \Magento\Store\App\Response\Redirect
{
    /**
     * @param string $url
     *
     * @return string
     */
    public function validateRedirectUrl($url)
    {
        $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/redirecturl1.log');
        $logger = new \Zend\Log\Logger();
        $logger->addWriter($writer);

        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();  
$request = $objectManager->get('Magento\Framework\App\Request\Http');  

$logger->info('after1'.$url);
        if (!$this->_isUrlInternal($url)) {
            $logger->info("here1");
            $logger->info($this->_storeManager->getStore()->getCode());
            
            if(!str_contains($url, 'products')){
                $url = $this->_storeManager->getStore()->getBaseUrl();
            }

            if(str_contains($url, 'checkout/cart')){
                $logger->info("cart");
                $url = $this->_storeManager->getStore()->getBaseUrl();
            }
        } else {
            // $logger->info("here2");
            // if(!str_contains($url, 'checkout/cart')){
            //     $logger->info("cart1111");
            //     $url = $this->_storeManager->getStore()->getBaseUrl();
            //     $url = $this->normalizeRefererUrl($url);
            // }else{
            //     if($request->getParam("store_code") == "senseon_configurator_store_view"){
            //         $logger->info($url);
            //         //$url = $this->_storeManager->getStore()->getBaseUrl();
            //         $url = str_replace("https://stage.accuride.com/en-us/customer/",$this->_storeManager->getStore()->getBaseUrl(),$url);
            //         $url = $this->normalizeRefererUrl($url);
            //         $logger->info("here redirect cart");
            //         $logger->info($url);
            //     }
            // }
            // $logger->info($url);
        }
        return $url;
    }
}
