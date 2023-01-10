<?php
/**
 * Alpine_DisableNewsletter
 *
 * @category    Alpine
 * @package     Alpine_DisableNewsletter
 * @copyright   Copyright (c) 2018 Alpine Consulting, Inc
 * @author      Aleksandr Mikhailov (aleksandr.mikhailov@alpineinc.com)
 */

namespace Alpine\DisableNewsletter\Block\Customer\Form;

use Alpine\DisableNewsletter\Helper\Data as DisableNewsletterHelper;
use Magento\Customer\Model\Session;
use Magento\Customer\Model\Url;
use Magento\Directory\Helper\Data;
use Magento\Directory\Model\ResourceModel\Country\CollectionFactory as CountryCollectionFactory;
use Magento\Directory\Model\ResourceModel\Region\CollectionFactory as RegionCollectionFactory;
use Magento\Framework\App\Cache\Type\Config;
use Magento\Framework\Json\EncoderInterface;
use Magento\Framework\Module\Manager;
use Magento\Framework\View\Element\Template\Context;

/**
 * Class Register
 *
 * @category    Alpine
 * @package     Alpine_DisableNewsletter
 */
class Register extends \Magento\Customer\Block\Form\Register
{
    /**
     * Alpine_DisableNewsletter Helper
     *
     * @var DisableNewsletterHelper
     */
    protected $helper;

    /**
     * Register constructor
     *
     * @param Context                  $context
     * @param Data                     $directoryHelper
     * @param EncoderInterface         $jsonEncoder
     * @param Config                   $configCacheType
     * @param RegionCollectionFactory  $regionCollectionFactory
     * @param CountryCollectionFactory $countryCollectionFactory
     * @param Manager                  $moduleManager
     * @param Session                  $customerSession
     * @param Url                      $customerUrl
     * @param DisableNewsletterHelper  $helper
     * @param array                    $data
     */
    public function __construct(
        Context $context,
        Data $directoryHelper,
        EncoderInterface $jsonEncoder,
        Config $configCacheType,
        RegionCollectionFactory $regionCollectionFactory,
        CountryCollectionFactory $countryCollectionFactory,
        Manager $moduleManager,
        Session $customerSession,
        Url $customerUrl,
        DisableNewsletterHelper $helper,
        array $data = []
    ) {
        $this->helper = $helper;

        parent::__construct(
            $context,
            $directoryHelper,
            $jsonEncoder,
            $configCacheType,
            $regionCollectionFactory,
            $countryCollectionFactory,
            $moduleManager,
            $customerSession,
            $customerUrl,
            $data
        );
    }

    /**
     * Newsletter module availability
     *
     * @return bool
     */
    public function isNewsletterEnabled()
    {
        return
            $this->helper->isNewsletterEnabled() &&
            $this->_moduleManager->isOutputEnabled('Magento_Newsletter');
    }
}