<?php
/**
 * Indusnet Technologies.
 *
 * @category  Indusnet
 * @package   Int_AggregationGraphQl
 * @author    Indusnet
 */
declare(strict_types=1);

namespace Int\AggregationGraphQl\Model\Resolver\Product\MediaGallery;

use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\ImageFactory;
use Magento\CatalogGraphQl\Model\Resolver\Products\DataProvider\Image\Placeholder as PlaceholderProvider;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;

/**
 * Returns media url
 */
class CrossImageGallery implements ResolverInterface
{
    /**
     * @var ImageFactory
     */
    private $productImageFactory;
    /**
     * @var PlaceholderProvider
     */
    private $placeholderProvider;
    protected $_storeManager;
    /**
     * @param ImageFactory $productImageFactory
     * @param PlaceholderProvider $placeholderProvider
     */
    public function __construct(
        ImageFactory $productImageFactory,
        PlaceholderProvider $placeholderProvider,
        \Magento\Catalog\Helper\Image $imageHelper,
            \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
            \Magento\Store\Model\StoreManagerInterface $storemanager
    ) {
        $this->productImageFactory = $productImageFactory;
        $this->placeholderProvider = $placeholderProvider;
        $this->imageHelper = $imageHelper;
        $this->productRepository = $productRepository;
        $this->_storeManager =  $storemanager;
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
        

        if (!isset($value['model'])) {
            throw new LocalizedException(__('"model" value should be specified'));
        }




        /** @var Product $product */
        $product = $value['model'];
        $store = $this->_storeManager->getStore();
        $media_url = $store->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
        $image_url = null;
        $_product = $this->productRepository->getById($product->getEntityId());

    
        $image_url = $media_url.'catalog/product'.$_product->getCrossSectionImage();
        if($_product->getCrossSectionImage() == 'no_selection' || $_product->getCrossSectionImage() == "" || $_product->getCrossSectionImage() == null || $image_url == $media_url) {
            $image_url = null;
           
        }
        
        return $image_url;
       
    }

}