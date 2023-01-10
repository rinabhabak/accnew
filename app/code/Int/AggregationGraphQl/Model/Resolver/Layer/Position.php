<?php
/**
 * Indusnet Technologies.
 *
 * @category  Indusnet
 * @package   Int_AggregationGraphQl
 * @author    Indusnet
 */
declare(strict_types=1);

namespace Int\AggregationGraphQl\Model\Resolver\Layer;

use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Framework\Exception\LocalizedException;
use Magento\Store\Model\ScopeInterface;

/**
 * Format product's Logos information for uom field
 */
class Position implements ResolverInterface
{

    /**
     * @var \Magento\Eav\Model\Config
     */
    protected $_eavConfig;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $_scopeConfig;

    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Eav\Model\Config $eavConfig
    ){
       $this->_scopeConfig =  $scopeConfig;
       $this->_eavConfig = $eavConfig;
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
        
        if (!isset($value['attribute_code'])) {
            throw new LocalizedException(__('"attribute_code" value should be specified'));
        }

        $attributeCode = \preg_replace('~_bucket$~', '', $value['attribute_code']);

        if($this->getConfig($attributeCode, 'position') !== null){
            return $this->getConfig($attributeCode, 'position');
        } else if($attributeCode === 'stock_status'){
            return $this->getConfig('stock', 'position');
        } else {
            $attribute = $this->_eavConfig->getAttribute('catalog_product', $attributeCode);
            return $attribute->getPosition();
        }
        
    }

    /**
     * @param string $attributeCode
     * @param string $configName
     * @return int
     */
    private function getConfig($attributeCode, $configName)
    {
        return $this->_scopeConfig->getValue(
            'amshopby/' . $attributeCode . '_filter/' . $configName,
            ScopeInterface::SCOPE_STORE
        );
    }

}