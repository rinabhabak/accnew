<?php
/**
 * Add SKU to product captions
 *
 * @category    Alpine
 * @package     Alpine_ProductImageCaption
 * @copyright   Copyright (c) 2018 Alpine Consulting, Inc
 * @author      Dmitry Naumov <dmitry.naumov@alpineinc.com>
 */

namespace Alpine\ProductImageCaption\Plugin\Block\Product\View;

use Magento\Catalog\Block\Product\Context;
use Magento\Catalog\Block\Product\View\Gallery as BaseImageGallery;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\Json\EncoderInterface;
use Magento\Framework\Stdlib\ArrayUtils;
use Psr\Log\LoggerInterface;

/**
 * Add SKU to product captions
 *
 * @category    Alpine
 * @package     Alpine_ProductImageCaption
 */
class Gallery extends BaseImageGallery
{
    /**
     * Json serializer
     *
     * @var Json
     */
    protected $jsonSerializer;

    /**
     * Logger
     *
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * Constructor
     *
     * @param Context $context
     * @param ArrayUtils $arrayUtils
     * @param EncoderInterface $jsonEncoder
     * @param Json $jsonSerializer
     * @param LoggerInterface $logger
     * @param array $data
     */
    public function __construct(
        Context $context,
        ArrayUtils $arrayUtils,
        EncoderInterface $jsonEncoder,
        Json $jsonSerializer,
        LoggerInterface $logger,
        array $data = []
    ) {
        $this->jsonSerializer = $jsonSerializer;
        $this->logger         = $logger;
        parent::__construct($context, $arrayUtils, $jsonEncoder, $data);
    }

    /**
     * After get media gallery data json
     *
     * @param BaseImageGallery $subject
     * @param string $result
     *
     * @return string
     */
    public function afterGetGalleryImagesJson(BaseImageGallery $subject, $result)
    {
        try {
            $imagesItems = $this->jsonSerializer->unserialize($result);

            foreach ($imagesItems as &$imagesItem) {
                if (array_key_exists('caption', $imagesItem) && ($imagesItem['caption'])) {
                    $imagesItem['caption'] = $this->getProduct()->getSku() . ' ' . $imagesItem['caption'];
                }
            }

            $result = $this->jsonSerializer->serialize($imagesItems);
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
        }

        return $result;
    }
}
