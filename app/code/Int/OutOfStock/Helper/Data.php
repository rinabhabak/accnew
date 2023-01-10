<?php
/**
 * @author Indusnet Team
 * @package Int_OutOfStock
 */

namespace Int\OutOfStock\Helper;

use Magento\Framework\App\Action\Action;

/**
 * Class Data
 */
class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;


    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    ) {
        parent::__construct($context);
        $this->storeManager = $storeManager;
    }

   

    /**
     * @param $type
     * @return string
     */
    public function getSubscribtionUrl()
    {
        return $this->_getUrl('outofstock/form/post/');
    }

    
}
