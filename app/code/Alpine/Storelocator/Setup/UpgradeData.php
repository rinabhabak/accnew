<?php
/**
 * Alpine_Storelocator
 *
 * @category    Alpine
 * @package     Alpine_Storelocator
 * @copyright   Copyright (c) 2018 Alpine Consulting, Inc
 * @author      Andrey Nesterov <andrey.nesterov@alpineinc.com>
 * @author      Aleksandr Mikhailov (aleksandr.mikhailov@alpineinc.com)
 */

namespace Alpine\Storelocator\Setup;

use Magento\Cms\Model\BlockFactory;
use Magento\Cms\Model\BlockRepository;
use Magento\Framework\App\State;
use Magento\Framework\App\Config\ScopeConfigInterface; 
use Magento\Framework\App\Config\Storage\WriterInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\UpgradeDataInterface;
use Amasty\Storelocator\Model\AttributeFactory;
use Alpine\Storelocator\Model\Attribute;
use Magento\Framework\UrlInterface;

/**
 * Alpine\Storelocator\Setup\UpgradeData
 *
 * @category    Alpine
 * @package     Alpine_Storelocator
 */
class UpgradeData implements UpgradeDataInterface
{
    /**
     * Attribute factory
     *
     * @var AttributeFactory
     */
    protected $attributeFactory;

    /**
     * Block Factory
     *
     * @var BlockFactory
     */
    protected $blockFactory;

    /**
     * Block Repository
     *
     * @var BlockRepository
     */
    protected $blockRepository;

    /**
     * URL Interface
     *
     * @var UrlInterface
     */
    protected $urlBuilder;

    /**
     * App State
     *
     * @var State
     */
    protected $appState;
    
    /**
    * App ScopeConfigInterface
    *
    * @var ScopeConfigInterface
    */
    protected $scopeConfig;

    /**
    * App WriterInterface
    *
    * @var WriterInterface
    */
    protected $configWriter;

    /**
     * Default value for search radius value
     * 
     * @var string
     */
    const DEAFULT_SEARCH_RADIUS_VALUE = 'Miles from my location';

    /**
     * Constructor
     *
     * @param AttributeFactory $attributeFactory
     * @param BlockFactory     $blockFactory
     * @param BlockRepository  $blockRepository
     * @param UrlInterface     $urlBuilder
     * @param State            $appState
     */
    public function __construct(
        AttributeFactory $attributeFactory,
        BlockFactory $blockFactory,
        BlockRepository $blockRepository,
        UrlInterface $urlBuilder,
        State $appState,
        ScopeConfigInterface $scopeConfig,
        WriterInterface $configWriter
    ) {
        $this->attributeFactory = $attributeFactory;
        $this->blockFactory     = $blockFactory;
        $this->blockRepository  = $blockRepository;
        $this->urlBuilder       = $urlBuilder;
        $this->appState         = $appState;
        $this->scopeConfig     = $scopeConfig;
        $this->configWriter    = $configWriter;
    }

    /**
     * Upgrade data
     *
     * @param ModuleDataSetupInterface $setup
     * @param ModuleContextInterface   $context
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     */
    public function upgrade(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();

        $attributeData = [
            'attribute_code' => Attribute::INDUSTRY_CODE,
            'frontend_label' => 'Industry',
            'frontend_input' => 'select'
        ];

        $options = [
            'option' => [
                'value'  => [
                    1 => [
                        'CH', 'Woodworking & Architectural'
                    ],
                    2 => [
                        'IE', 'Industrial & Electromechanical'
                    ],
                    3 => [
                        'All', 'All'
                    ]
                ],
                'delete' => [
                    1 => '',
                    2 => '',
                    3 => ''
                ]
            ]
        ];

        if (version_compare($context->getVersion(), '0.2.0') < 0) {
            $attribute = $this->attributeFactory->create()
                ->setData($attributeData);
            $attribute->save();

            $attribute->getResource()->saveOptions($options, $attribute->getId());
        }

        if (version_compare($context->getVersion(), '0.3.0') < 0) {
            $this->createBlockForStoreLocator();
        }
        
        if(version_compare($context->getVersion(), '0.3.2') < 0) {
            $this->changeLabelIndustryAttribute();
        }

        $setup->endSetup();
    }

    /**
     * Create block for "Store Locator" page
     *
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     */
    protected function createBlockForStoreLocator()
    {
        $url = $this->appState->emulateAreaCode('frontend', [$this->urlBuilder, 'getUrl'], ['contact']);

        $expr1 = __('Accuride products are offered through a worldwide network of authorized distributors.  We work side-by-side with our distributors to ensure the highest level of support and expert advice.');
        $expr2 = __('Need help?  We’d be happy to point you in the right direction—please <a href="' . $url . '">Contact Us</a> directly.');

        $blockContent = <<<BlockContent
<span>{$expr1}</span>
<span>{$expr2}</span>
BlockContent;

        $blockData = [
            'title'      => 'Store Locator Description',
            'identifier' => 'amlocator-description',
            'content'    => $blockContent,
            'is_active'  => 1,
            'stores'     => [0],
            'sort_order' => 0
        ];

        $block = $this->blockFactory->create()->setData($blockData);
        $this->blockRepository->save($block);
    }
    
    /**
    * change frontend label form "Industry" to "Select Industry"
    */
    protected function changeLabelIndustryAttribute()
    {   
        //load attribute
        $attribute = $this->attributeFactory->create()->load('Industry', 'frontend_label');
        // change label
        $attribute
            ->setFrontendLabel('Select Industry')
            ->save();  

        //get all search radiuses and prepare array
        $searchRadius = $this->scopeConfig->getValue(
            'amlocator/locator/radius'
        );
        $delimiter = ',';
        $searchRadius = explode($delimiter, $searchRadius);

        //add new value and set it back
        array_push($searchRadius, self::DEAFULT_SEARCH_RADIUS_VALUE);
        $searchRadius = implode($delimiter, $searchRadius);
        $this->configWriter->save('amlocator/locator/radius', $searchRadius);
    }
}
