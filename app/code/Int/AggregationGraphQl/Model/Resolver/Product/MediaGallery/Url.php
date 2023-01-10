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
class Url implements ResolverInterface
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
        \Magento\Store\Model\StoreManagerInterface $storemanager
    ) {
        $this->productImageFactory = $productImageFactory;
        $this->placeholderProvider = $placeholderProvider;
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
        if (!isset($value['image_type']) && !isset($value['file'])) {
            throw new LocalizedException(__('"image_type" value should be specified'));
        }

        if (!isset($value['model'])) {
            throw new LocalizedException(__('"model" value should be specified'));
        }

        /** @var Product $product */
        $product = $value['model'];
        $store = $this->_storeManager->getStore();
        $media_url = $store->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);

        if (isset($value['image_type'])) {
            $imagePath = $product->getData($value['image_type']);
            return $media_url.'catalog/product'.$imagePath;
        }
        if (isset($value['file'])) {
            $image = $this->productImageFactory->create();
            $image->setDestinationSubdir('image')->setBaseFile($value['file']);
            $imageUrl = $image->getUrl();
            return $media_url.'catalog/product'.$value['file'];
        }
        return [];
    }

}
