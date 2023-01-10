<?php
/**
 * Alpine_OrdersExport
 *
 * @category    Alpine
 * @package     Alpine_OrdersExport
 * @copyright   Copyright (c) 2018 Alpine Consulting, Inc
 * @author      Alex Didenko <alex.didenko@alpineinc.com>
 */

namespace Alpine\OrdersExport\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Directory\Model\CurrencyFactory;

/**
 * Alpine\OrdersExport\Helper\Data
 *
 * @category    Alpine
 * @package     Alpine_Photoshelter
 */
class Data extends AbstractHelper
{
    /**
     * Store manager
     *
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * Currency factory
     *
     * @var CurrencyFactory
     */
    protected $currencyFactory;

    /**
     * Construct
     *
     * @param StoreManagerInterface $storeManager
     * @param CurrencyFactory $currencyFactory
     * @param Context $context
     */
    public function __construct(
        StoreManagerInterface $storeManager,
        CurrencyFactory $currencyFactory,
        Context $context
    ) {
        parent::__construct($context);
        $this->storeManager = $storeManager;
        $this->currencyFactory = $currencyFactory;
    }

    /**
     * Get current currency symbol
     *
     * @return string
     */
    public function getCurrencySymbol()
    {
        $currentCurrencyCode = $this->storeManager->getStore()->getCurrentCurrencyCode();
        $currency = $this->currencyFactory->create()->load($currentCurrencyCode);

        return $currency->getCurrencySymbol();
    }

    /**
     * Set array value as global key
     *
     * @param array $arr
     * @param string $keyAsIndex
     * @param string $keyAsValue
     * @return array
     */
    public function setValueAsGlobalKey($arr, $keyAsIndex, $keyAsValue = null)
    {
        $arr2 = [];

       foreach ($arr as $k => $v) {
           if (isset($v[$keyAsIndex])) {
               $arr2[$v[$keyAsIndex]] = $keyAsValue ? $v[$keyAsValue] : $v;
           }
       }

        return $arr2;
    }

    /**
     * Unset array values
     *
     * @param array $arr
     * @param mixed $default]
     * @return array
     */
    public function unsetValues($arr, $default = '')
    {
        foreach ($arr as $k => $v) {
            $arr[$k] = $default;
        }

        return $arr;
    }
}
