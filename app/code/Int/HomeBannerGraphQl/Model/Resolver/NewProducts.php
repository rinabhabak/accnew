<?php
/**
 * Indusnet Technologies.
 *
 * @category  Indusnet
 * @package   Int_HomeBannerGraphQl
 * @author    Indusnet
 */

namespace Int\HomeBannerGraphQl\Model\Resolver;

use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Exception\GraphQlNoSuchEntityException;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Class Slider
 * @package Int\HomeBannerGraphQl\Model\Resolver
 */
class NewProducts implements ResolverInterface
{

    private $_newProductsDataProvider;

    /**
     * @param Int\HomeBannerGraphQl\Model\Resolver\DataProvider\NewProducts $NewProductsDataProvider
     */
    public function __construct(
        \Int\HomeBannerGraphQl\Model\Resolver\DataProvider\NewProducts $newProductsDataProvider
    ) {
        $this->_newProductsDataProvider = $newProductsDataProvider;
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

        $resultData =  $this->_newProductsDataProvider->getBannerData($args);
        return $resultData;
    }

}