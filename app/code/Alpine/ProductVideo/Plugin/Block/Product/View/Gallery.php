<?php
/**
 * Alpine_ProductVideo
 *
 * @category    Alpine
 * @package     Alpine_ProductVideo
 * @copyright   Copyright (c) 2018 Alpine Consulting, Inc
 * @author      Andrey Nesterov <andrey.nesterov@alpineinc.com>
 */
namespace Alpine\ProductVideo\Plugin\Block\Product\View;

use Magento\Catalog\Block\Product\View\Gallery as BaseImageGallery;
use Magento\ProductVideo\Block\Product\View\Gallery as BaseVideoGallery;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\Registry;
use Psr\Log\LoggerInterface;

/**
 * Alpine\ProductVideo\Plugin\Block\Product\View\Gallery
 *
 * @category    Alpine
 * @package     Alpine_ProductVideo
 */
class Gallery
{
    /**
     * Video attributes codes
     *
     * @var array
     */
    protected $videoAttributes = [
        'video1_url',
        'video2_url',
        'video3_url'
    ];
    
    /**
     * Json serializer
     *
     * @var Json
     */
    protected $jsonSerializer;
    
    /**
     * Registry
     *
     * @var Registry
     */
    protected $registry;

    /**
     * Logger
     *
     * @var LoggerInterface
     */
    protected $logger;
    
    /**
     * Constructor
     *
     * @param Json $jsonSerializer
     * @param Registry $registry
     * @param LoggerInterface $logger
     */
    public function __construct(
        Json $jsonSerializer,
        Registry $registry,
        LoggerInterface $logger
    ) {
        $this->jsonSerializer = $jsonSerializer;
        $this->registry = $registry;
        $this->logger = $logger;
    }
    
    /**
     * After get media gallery data json
     *
     * @param BaseImageGallery $subject
     * @param string $result
     * @return string
     */
    public function afterGetGalleryImagesJson(
        BaseImageGallery $subject,
        $result
    ) {
        try {
            $imagesItems = $this->jsonSerializer->unserialize($result);
            
            foreach ($this->getProductVideos() as $videoUrl) {
                $image = $this->getImageByVideoUrl($videoUrl);
                $imagesItems[] = [
                    'thumb' => $image,
                    'img' => $image,
                    'full' => $image,
                    'caption' => '',
                    'position' => $this->getPosition($imagesItems),
                    'isMain' => false,
                    'type' => 'video',
                    'videoUrl' => $videoUrl
                ];
            }
            
            $result = $this->jsonSerializer->serialize($imagesItems);
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
        }
        
        return $result;
    }

    /**
     * After get media gallery data json
     *
     * @param BaseVideoGallery $subject
     * @param string $result
     * @return string
     */
    public function afterGetMediaGalleryDataJson(
        BaseVideoGallery $subject,
        $result
    ) {
        try {
            $mediaGalleryData = $this->jsonSerializer->unserialize($result);
            
            foreach ($this->getProductVideos() as $videoUrl) {
                $mediaGalleryData[] = [
                    'mediaType' => 'external-video',
                    'videoUrl' => $videoUrl,
                    'isBase' => false
                ];
            }
            
            $result = $this->jsonSerializer->serialize($mediaGalleryData);
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
        }
        
        return $result;
    }
    
    /**
     * Get product videos
     *
     * @return array
     */
    protected function getProductVideos()
    {
        $result = [];
        $product = $this->registry->registry('product');
        
        foreach ($this->videoAttributes as $code) {
            if ($videoUrl = $product->getData($code)) {
                $result[] = $videoUrl;
            }
        }
        
        return $result;
    }
    
    /**
     * Get image by video url
     *
     * @param string $videoUrl
     * @return string
     */
    protected function getImageByVideoUrl($videoUrl)
    {
        parse_str(parse_url($videoUrl, PHP_URL_QUERY), $urlParams);
        $videoId = $urlParams['v'];
        
        return 'https://i.ytimg.com/vi/' . $videoId . '/hqdefault.jpg';
    }
    
    /**
     * Get position
     *
     * @param array $media
     * @return int
     */
    protected function getPosition(array $media)
    {
        $result = 1;
        $lastElement = end($media);
        
        if ($lastElement && isset($lastElement['position'])) {
            $result = intval($lastElement['position']) + 1;
        }
        
        return $result;
    }
}
