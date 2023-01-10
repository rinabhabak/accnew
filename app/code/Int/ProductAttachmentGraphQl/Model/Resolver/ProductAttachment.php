<?php
/**
 * Indusnet Technologies.
 *
 * @category  Indusnet
 * @package   Int_ProductAttachmentGraphQl
 * @author    Indusnet
 */

namespace Int\ProductAttachmentGraphQl\Model\Resolver;

use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Exception\GraphQlNoSuchEntityException;
use Magento\Framework\Exception\NoSuchEntityException;
use Amasty\ProductAttachmentApi\Model\FrontendAttachment;


/**
 * Class Slider
 * @package Int\ProductAttachmentGraphQl\Model\Resolver
 */
class ProductAttachment implements ResolverInterface
{

    private $_productAttachmentDataProvider;
    private $_FrontendAttachment;

    /**
     * @param Int\ProductAttachmentGraphQl\Model\Resolver\DataProvider\ProductAttachment $NewProductsDataProvider
     */
    public function __construct(
        \Int\ProductAttachmentGraphQl\Model\Resolver\DataProvider\ProductAttachment $ProductAttachmentDataProvider,
        FrontendAttachment $frontendAttachment
    ) {
        $this->_productAttachmentDataProvider = $ProductAttachmentDataProvider;
        $this->_FrontendAttachment = $frontendAttachment;
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
        $frontendAttach =  $this->_FrontendAttachment->getByProductId($args['input']['productId']);
        $resultData =  $this->_productAttachmentDataProvider->getProductAttachmentData($frontendAttach,$args['input']['productId']);
        return $resultData;
    }

}