<?php
/**
 * Alpine_Storelocator
 *
 * @category    Alpine
 * @package     Alpine_Storelocator
 * @copyright   Copyright (c) 2018 Alpine Consulting, Inc
 * @author      Andrey Nesterov <andrey.nesterov@alpineinc.com>
 */
namespace Alpine\Storelocator\Plugin\Block;

use Amasty\Storelocator\Block\Location as BaseLocation;
use Alpine\Storelocator\Model\Attribute;
use Magento\Framework\Stdlib\CookieManagerInterface;

/**
 * Alpine\Storelocator\Plugin\Block\Location
 *
 * @category    Alpine
 * @package     Alpine_Storelocator
 */
class Location
{
    /**
     * Constant for KEY = 'All'
     *
     * @var string
     */
    const CONST_KEY_ALL = 'All';

    /**
     * Industry map
     *
     * @var array
     */
    protected $industryMap = [
        'CH' => 'Woodworking & Architectural',
        'IE' => 'Industrial & Electromechanical',
        self::CONST_KEY_ALL => 'All'
    ];
    
    /**
     * Constant for industry attribute code
     *
     * @var string
     */
    const CONST_INDUSTRY_CODE = 'industry';

    /**
     * Cookie manager
     *
     * @var CookieManagerInterface
     */
    protected $cookieManager;
    
    /**
     * Attribute model
     *
     * @var Attribute
     */
    protected $attributeModel;

    /**
     * Constructor
     *
     * @param CookieManagerInterface $cookieManager
     * @param Attribute $attributeModel
     */
    public function __construct(
        CookieManagerInterface $cookieManager,
        Attribute $attributeModel
    ) {
        $this->cookieManager = $cookieManager;
        $this->attributeModel = $attributeModel;
    }
    
    /**
     * After get attributes
     *
     * @param BaseLocation $subject
     * @param array $result
     * @return array
     */
    public function afterGetAttributes(
        BaseLocation $subject,
        $result
    ) {
        $selectedIndustry = false;
        $productId = $this->cookieManager->getCookie('product');
        $attributeId = $this->attributeModel->getAttributeIdByCode(self::CONST_INDUSTRY_CODE);
        if ($productId) {
            $product = $subject->getProductById($productId);
            $selectedIndustry = $product->getResource()
                ->getAttribute(self::CONST_INDUSTRY_CODE)
                ->getFrontend()
                ->getValue($product);
            if ($selectedIndustry) {
                $selectedIndustry = explode(', ', $selectedIndustry);
            }
        }

        $selectedIndustryCount = $selectedIndustry ? count($selectedIndustry) : 0;

        foreach ($result as $i => $attribute) {
            if (isset($attribute['attribute_id'])
                && $attribute['attribute_id'] == $attributeId
                && isset($attribute['options'])
            ) {
                if ($selectedIndustry && $selectedIndustryCount > 0) {
                    if ($selectedIndustryCount > 1) {
                        $selectedIndustry = [self::CONST_KEY_ALL];
                    }
                    foreach ($attribute['options'] as $index => $option) {
                        if ($this->industryMap[$selectedIndustry[0]] == $option) {
                            $result[$i]['selected'] = $index;
                            break;
                        }
                    }
                }
                break;
            }
        }

        return $result;
    }
}
