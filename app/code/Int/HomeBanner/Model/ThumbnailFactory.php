<?php
/**
 * Indusnet Technologies.
 *
 * @category  Indusnet
 * @package   Int_HomeBanner
 * @author    Indusnet
 */
namespace Int\HomeBanner\Model;

class ThumbnailFactory
{
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $_objectManager;

    /**
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     */
    public function __construct(\Magento\Framework\ObjectManagerInterface $objectManager)
    {
        $this->_objectManager = $objectManager;
    }

    /**
     * Create new PagSlider model
     *
     * @param array $arguments
     * @return \Int\HomeBanner\Model\PagSlider
     */
    public function create(array $arguments = [])
    {
        return $this->_objectManager->create('Int\HomeBanner\Model\Thumbnail', $arguments, false);
    }
}