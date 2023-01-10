<?php
/**
 * Slider Selector Block
 *
 * @category    Alpine
 * @package     Alpine_Theme
 * @copyright   Copyright (c) 2018 Alpine Consulting, Inc
 * @author      Dmitry Naumov <dmitry.naumov@alpineinc.com>
 * @author      Andrey Nesterov <andrey.nesterov@alpineinc.com>
 */
namespace Alpine\Theme\Block;

use Magento\Catalog\Model\CategoryRepository;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;

/**
 * Slider Selector Block
 *
 * @category    Alpine
 * @package     Alpine_Theme
 */
class SliderSelector extends Template
{
    /**
     * Attributes in slide selector
     *
     * @var array
     */
    protected $attributesCodes = [
        'market',           'length',
        'side_space',       'load_rating',
        'special_features', 'finish',
        'material',         'mounting',
        'extension'
    ];

    /**
     * Category repository
     *
     * @var CategoryRepository
     */
    protected $categoryRepository;

    /**
     * Constructor
     *
     * @param Context $context
     * @param CategoryRepository $categoryRepository
     * @param array $data
     */
    public function __construct(
        Context $context,
        CategoryRepository $categoryRepository,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->categoryRepository = $categoryRepository;
    }

    /**
     * Get Slide Selector Url
     *
     * @return string
     */
    public function getProductsUrl()
    {
        $rootCategoryId = $this->_storeManager->getStore()->getRootCategoryId();
        try {
            $rootCategory = $this->categoryRepository->get($rootCategoryId);
            $defaultCategory = $rootCategory->getChildrenCategories()->getFirstItem();
            if (!$defaultCategory->getId()) {
                return '';
            }
        } catch (\Exception $e) {
            $this->_logger->error($e->getMessage());
            return '';
        }

        return $defaultCategory->getUrl();
    }
    
    /**
     * Get attributes codes
     *
     * @return array
     */
    public function getAttributesCodes()
    {
        return $this->attributesCodes;
    }
}
