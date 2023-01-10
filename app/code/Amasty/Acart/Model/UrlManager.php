<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Acart
 */


namespace Amasty\Acart\Model;

class UrlManager extends \Magento\Framework\DataObject
{
    
    /**
     * @var \Amasty\Acart\Model\Rule
     */
    private $rule;

    /**
     * @var \Amasty\Acart\Model\History
     */
    private $history;

    /**
     * @var \Magento\Framework\Url
     */
    private $frontUrlModel;

    const PATH_URL_REGEXP_PATTERN = '/[^a-zA-Z0-9_ %\[\]\:\.\(\)%&-\/]/s';

    protected $_googleAnalyticsParams
        = [
            'utm_source',
            'utm_medium',
            'utm_term',
            'utm_content',
            'utm_campaign'
        ];

    public function __construct(
        \Magento\Framework\Url $frontUrlModel,
        array $data = []
    ) {
        $this->frontUrlModel = $frontUrlModel;

        parent::__construct($data);
    }

    public function init(
        \Amasty\Acart\Model\Rule $rule,
        \Amasty\Acart\Model\History $history
    ) {
        $this->rule = $rule;
        $this->history = $history;

        return $this;
    }

    public function getRule()
    {
        return $this->rule;
    }

    /**
     * Add params to URL query
     * add google Analytics
     *
     * @since 1.0.6 Google Analitics moved to _query (after ?)
     *
     * @param array $params
     *
     * @return array
     */
    protected function getParams($params = [])
    {
        $params['id'] = $this->history->getId();
        $params['key'] = $this->history->getPublicKey();
        $params['_scope'] = $this->history->getStore()->getId();
        $params['_nosid'] = true;
        $params['_query'] = ['___store' => $this->history->getStore()->getCode()];

        return array_merge($params, $this->getUtmParams());
    }

    /**
     * @return array
     */
    public function getUtmParams()
    {
        $params = [];

        foreach ($this->_googleAnalyticsParams as $param) {
            $val = $this->rule->getData($param);
            if (!empty($val)) {
                $params["_query"][$param] = $val;
            }
        }

        return $params;
    }

    public function get($url)
    {

        $objectManager =  \Magento\Framework\App\ObjectManager::getInstance();
        $storeManager = $objectManager->get('\Magento\Store\Model\StoreManagerInterface');
        $storeData = $storeManager->getStore($this->getParams()['_scope']);
        $storeCode = (string)$storeData->getCode();
        $storeId = (string)$storeData->getId();
        $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/newab1.log');
        $logger = new \Zend\Log\Logger();
        $logger->addWriter($writer);
        // return $this->frontUrlModel->getUrl(
        //     'amasty_acart/email/url',
        //     $this->getParams(['url' => urlencode(base64_encode($url))])
        // );

        $url = $this->frontUrlModel->getUrl(
            'amasty_acart/email/url',
            $this->getParams(['url' => urlencode(base64_encode($url)),'store_code' => $storeCode])
        );

        $val = str_replace($storeManager->getStore($storeId)->getBaseUrl(),"https://stage.accuride.com/",$url);

        // if($storeCode == "senseon_configurator_store_view"){
            
        //     $logger->info("here - Ecom");
        // }else{
        //     $val = str_replace("en-us/","",$url);
        //     $logger->info("here - NA");
        // }


        $logger->info("url".$storeId);
        $logger->info($val);
        $logger->info($storeCode);
        return $val;
    }

    public function mageUrl($url)
    {
        $objectManager =  \Magento\Framework\App\ObjectManager::getInstance();
        $storeManager = $objectManager->get('\Magento\Store\Model\StoreManagerInterface');
        $storeData = $storeManager->getStore($this->getParams()['_scope']);
        $storeCode = (string)$storeData->getCode();
        $storeId = (string)$storeData->getId();
        $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/newabmage1.log');
        $logger = new \Zend\Log\Logger();
        $logger->addWriter($writer);
        $url =  $this->frontUrlModel->getUrl(
            'amasty_acart/email/url',
            $this->getParams(['mageUrl' => urlencode(base64_encode($url)),'return_cart' => "1",'store_code' => $storeCode])
        );
        $val = str_replace($storeManager->getStore($storeId)->getBaseUrl(),"https://stage.accuride.com/",$url);
        return $val;
    }

    public function frontUrl()
    {
        return $this->frontUrlModel->getUrl();
    }

    public function unsubscribeUrl()
    {
        return $this->frontUrlModel->getUrl('amasty_acart/email/unsubscribe', $this->getParams());
    }

    /**
     * @param $urlString
     *
     * @return string
     */
    public function getCleanUrl($urlString)
    {
        return preg_replace(self::PATH_URL_REGEXP_PATTERN, '', $urlString);
    }
}
