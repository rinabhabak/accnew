<?php
/**
 * Alpine_Storelocator
 *
 * @category    Alpine
 * @package     Alpine_Storelocator
 * @copyright   Copyright (c) 2019 Alpine Consulting, Inc
 * @author      Evgeniy Derevyanko <evgeniy.derevyanko@alpineinc.com>
 * @author      Anton Smolenov <anton.smolenov@alpineinc.com>
 */
namespace Alpine\Storelocator\Block;

use Alpine\Storelocator\Helper\Data as LocatorHelper;

/**
 * Alpine\Storelocator\Block
 *
 * @category    Alpine
 * @package     Alpine_Storelocator
 */
class Location extends \Amasty\Storelocator\Block\Location
{
    /**
     * @var LocatorHelper
     */
    protected $locatorHelper;

    /**
     * Location constructor.
     *
     * @param \Magento\Framework\View\Element\Template\Context              $context,
     * @param \Magento\Framework\ObjectManagerInterface                     $objectManager
     * @param \Magento\Framework\Registry                                   $coreRegistry
     * @param \Magento\Framework\Json\EncoderInterface                      $jsonEncoder
     * @param \Magento\Framework\Filesystem\Io\File                         $ioFile
     * @param \Amasty\Storelocator\Helper\Data                              $dataHelper
     * @param \Amasty\Storelocator\Model\ResourceModel\Attribute\Collection $attributeCollection
     * @param \Amasty\Base\Model\Serializer                                 $serializer
     * @param \Amasty\Storelocator\Model\ConfigProvider                      $configProvider
     * @param LocatorHelper                                                 $locatorHelper
     * @param array                                                         $data
     */
     public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Framework\Json\EncoderInterface $jsonEncoder,
        \Magento\Framework\Filesystem\Io\File $ioFile,
        \Amasty\Storelocator\Helper\Data $dataHelper,
        \Amasty\Storelocator\Model\ResourceModel\Attribute\Collection $attributeCollection,
        \Amasty\Base\Model\Serializer $serializer,
        \Amasty\Storelocator\Model\ConfigProvider $configProvider,
        \Amasty\Storelocator\Model\ImageProcessor $imageProcessor,
        \Magento\Catalog\Model\Product $productModel,
        \Amasty\Storelocator\Model\ResourceModel\Location\CollectionFactory $locationCollectionFactory,
        \Amasty\Storelocator\Model\BaseImageLocation $baseImageLocation,
        \Amasty\Storelocator\Api\Validator\LocationProductValidatorInterface $locationProductValidator,
        \Amasty\Storelocator\Api\ReviewRepositoryInterface  $reviewRepository,
        LocatorHelper $locatorHelper,
        array $data = []
    ) {
        parent::__construct(
            $context,
            // $objectManager,
            $coreRegistry,
            $jsonEncoder,
            $ioFile,
            $dataHelper,
            $attributeCollection,
            $serializer,
            $configProvider,
            $imageProcessor,
            $productModel,
            $locationCollectionFactory,
            $baseImageLocation,
            $locationProductValidator,
            $reviewRepository,
            $data
        );
         $this->locatorHelper = $locatorHelper;
    }

    public function getNoResultMessage() {
         return $this->locatorHelper->getConfigValue("alpine_store_locator/general/no_results_message");
    }
}
