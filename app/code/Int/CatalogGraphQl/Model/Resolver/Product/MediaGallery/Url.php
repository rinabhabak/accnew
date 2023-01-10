<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Int\CatalogGraphQl\Model\Resolver\Product\MediaGallery;

use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\ImageFactory;
use Magento\CatalogGraphQl\Model\Resolver\Products\DataProvider\Image\Placeholder as PlaceholderProvider;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Framework\View\ConfigInterface;

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

    /**
     * @var \Magento\Catalog\Helper\Image
     */
    protected $_catalogImageHelper;

    /**
     * @param ImageFactory $productImageFactory
     * @param PlaceholderProvider $placeholderProvider
     */
    public function __construct(
        ImageFactory $productImageFactory,
        PlaceholderProvider $placeholderProvider,
        \Magento\Catalog\Helper\Image $catalogImageHelper
    ) {
        $this->productImageFactory = $productImageFactory;
        $this->placeholderProvider = $placeholderProvider;
        $this->_catalogImageHelper = $catalogImageHelper;
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
        if (isset($value['image_type'])) {
            $imagePath = $product->getData($value['image_type']);
            return $this->getImageUrl($value['image_type'], $imagePath,$product, $args);
        }
        if (isset($value['file'])) {
            $image = $this->productImageFactory->create();
            $image->setDestinationSubdir('image')->setBaseFile($value['file']);
            $image ->setWidth($args['width'] ?? null);
            $image->setHeight($args['height'] ?? null);
            $p =$image->resize();
            $imageUrl = $image->getUrl();
            return $imageUrl;
        }
        return [];
    }

    /**
     * Get image URL
     *
     * @param string $imageType
     * @param string|null $imagePath
     * @param array $imageArgs
     * @return string
     * @throws \Exception
     */
    private function getImageUrl(string $imageType, ?string $imagePath,$product,array $imageArgs): string
    {

        $width = $imageArgs['width'] ?? null;
        $height = $imageArgs['height'] ?? null;
        $imageUrl = $this->_catalogImageHelper->init($product, 'product_page_image_large')
                        ->setImageFile($product->getData($imageType)) // image,small_image,thumbnail
                        ->resize($width,$height)
                        ->getUrl();
        return $imageUrl;
    }
}
