<?php
/**
 * Indusnet Technologies.
 *
 * @category  Indusnet
 * @package   Int_HomeBannerGraphQl
 * @author    Indusnet
 */
namespace Int\HomeBannerGraphQl\Model\Resolver\DataProvider;

use Magento\Framework\GraphQl\Exception\GraphQlNoSuchEntityException;
use Magento\Framework\Exception\NoSuchEntityException;

class SecondSlider
{
    protected $_bannerFactory;

    public function __construct(
        \Int\HomeBanner\Model\HomeSecondBannerFactory $HomeSecondBannerFactory
        )
    {
        $this->_bannerFactory  = $HomeSecondBannerFactory;
    }
    
    
    /**
     * Get banner data
     * @return array
     * @throws GraphQlNoSuchEntityException
     */
    public function getBannerData()
    {
        try {
            $collection = $this->_bannerFactory->create()->getCollection();
            //$collection->addFieldToFilter('is_active',1);
            $collection->setOrder('created_at', 'DESC');
            $homebannerData = $collection->getData();

        } catch (NoSuchEntityException $e) {
            throw new GraphQlNoSuchEntityException(__($e->getMessage()), $e);
        }
        return $homebannerData;
    }
}
