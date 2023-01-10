<?php
/**
 * Alpine_Catalog
 *
 * @category    Alpine
 * @package     Alpine_Catalog
 * @copyright   Copyright (c) 2018 Alpine Consulting, Inc
 * @author      Andrey Nesterov <andrey.nesterov@alpineinc.com>
 */
namespace Alpine\Catalog\Plugin\Helper;

use Magento\Catalog\Helper\Image as BaseImage;
use Psr\Log\LoggerInterface;

/**
 * Alpine\Catalog\Plugin\Helper\Image
 *
 * @category    Alpine
 * @package     Alpine_Catalog
 */
class Image
{
    /**
     * Logger
     *
     * @var LoggerInterface
     */
    protected $logger;
    
    /**
     * Constructor
     *
     * @param LoggerInterface $logger
     */
    public function __construct(
        LoggerInterface $logger
    ) {
        $this->logger = $logger;
    }

    /**
     * Return resized product image information
     *
     * @return array
     */
    public function aroundGetResizedImageInfo(
        BaseImage $subject,
        callable $proceed
    ) {
        try {
            $result = $proceed();
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
            $result = [];
        }
        
        return $result;
    }
}
