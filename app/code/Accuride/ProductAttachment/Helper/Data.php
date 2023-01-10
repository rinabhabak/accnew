<?php

namespace Accuride\ProductAttachment\Helper;
use \Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\UrlInterface;

class Data extends AbstractHelper
{
    public function getLinkIcon(){
        return $this->_urlBuilder->getBaseUrl(['_type' => UrlInterface::URL_TYPE_MEDIA]).'amasty/amfile/icon/external-link-symbol.png';
    }
}