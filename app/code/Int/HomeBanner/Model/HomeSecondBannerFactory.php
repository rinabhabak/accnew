<?php
/**
 * Indusnet Technologies.
 *
 * @category  Indusnet
 * @package   Int_HomeBanner
 * @author    Indusnet
 */
namespace Int\HomeBanner\Model;

class HomeSecondBannerFactory
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
     * Create new HomeSecondBanner model
     *
     * @param array $arguments
     * @return \Int\HomeBanner\Model\SecondSlider
     */
    public function create(array $arguments = [])
    {
        return $this->_objectManager->create('Int\HomeBanner\Model\SecondSlider', $arguments, false);
    }
}