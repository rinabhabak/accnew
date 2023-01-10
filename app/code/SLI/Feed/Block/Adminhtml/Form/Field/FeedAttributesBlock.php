<?php
/**
 * Copyright (c) 2015 S.L.I. Systems, Inc. (www.sli-systems.com) - All Rights Reserved
 * This file is part of Learning Search Connect.
 * Learning Search Connect is distributed under a limited and restricted
 * license � please visit www.sli-systems.com/LSC for full license details.
 *
 * THIS CODE AND INFORMATION ARE PROVIDED "AS IS" WITHOUT WARRANTY OF ANY
 * KIND, EITHER EXPRESSED OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE
 * IMPLIED WARRANTIES OF MERCHANTABILITY AND/OR FITNESS FOR A
 * PARTICULAR PURPOSE. TO THE MAXIMUM EXTENT PERMITTED BY APPLICABLE LAW, IN NO
 * EVENT WILL SLI BE LIABLE TO YOU OR ANY OTHER PARTY FOR ANY GENERAL, DIRECT,
 * INDIRECT, SPECIAL, INCIDENTAL OR CONSEQUENTIAL LOSS OR DAMAGES OF ANY
 * CHARACTER ARISING OUT OF THE USE OF THE CODE AND/OR THE LICENSE INCLUDING
 * BUT NOT LIMITED TO PERSONAL INJURY, LOSS OF DATA, LOSS OF PROFITS, LOSS OF
 * ASSIGNMENTS, DATA OR OUTPUT FROM THE SERVICE BEING RENDERED INACCURATE,
 * FAILURE OF CODE, SERVER DOWN TIME, DAMAGES FOR LOSS OF GOODWILL, BUSINESS
 * INTERRUPTION, COMPUTER FAILURE OR MALFUNCTION, OR ANY AND ALL OTHER DAMAGES
 * OR LOSSES OF WHATEVER NATURE, EVEN IF SLI HAS BEEN INFORMED OF THE
 * POSSIBILITY OF SUCH DAMAGES.
 */

namespace SLI\Feed\Block\Adminhtml\Form\Field;

use Magento\Eav\Api\AttributeRepositoryInterface;
use Magento\Framework\Api\SearchCriteria;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\View\Element\Context;
use Magento\Framework\View\Element\Html\Select;

/**
 * HTML select element block with feed attributes
 */
class FeedAttributesBlock extends Select
{

    /**
     * @var SearchCriteriaBuilder
     */
    protected $_searchCriteriaBuilder;

    /**
     * @var AttributeRepositoryInterface
     */
    protected $_eavAttributeRepository;

    /**
     * @var SearchCriteria
     */
    protected $_searchCriteria;

    protected $_productAttributes;

    /**
     * We want these attributes to appear in the drop down configuration menu, but they are not included in the
     * EAV selection. We must add them in individually.
     *
     * @var []
     */
    protected $nonEavAttributes = [
        'related_products',
        'upsell_products',
        'crosssell_products',
        'reviews_count',
        'rating_summary',
        'stock_summary'
    ];

    /**
     * Construct
     *
     * @param \Magento\Framework\View\Element\Context|Context $context
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param AttributeRepositoryInterface $eavAttributeRepository
     * @param array $data
     */
    public function __construct(
        Context $context,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        AttributeRepositoryInterface $eavAttributeRepository,
        array $data = []
    ) {
        parent::__construct($context, $data);

        $this->_searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->_searchCriteria = $this->_searchCriteriaBuilder
            ->setPageSize(1000)
            ->create();

        $this->_eavAttributeRepository = $eavAttributeRepository;
    }

    /**
     * Load a list of attributes that are available to display
     *
     * @return array|null
     */
    protected function _getProductAttributesList()
    {
        if ($this->_productAttributes === null) {
            $this->_productAttributes = [];
            $attributes = $this->_eavAttributeRepository
                ->getList('catalog_product', $this->_searchCriteria)->getItems();
            foreach ($attributes as $attribute) {
                array_push($this->_productAttributes, $attribute->getAttributeCode());
            }
            // Add our non eav attributes to the list
            $this->_productAttributes = array_merge($this->_productAttributes, $this->nonEavAttributes);

            asort($this->_productAttributes);
        } else {
            return $this->_productAttributes;
        }

        return $this->_productAttributes;
    }

    /**
     * Set Name
     *
     * @param string $value
     * @return $this
     */
    public function setInputName($value)
    {
        return $this->setName($value);
    }

    /**
     * Render block HTML
     *
     * @return string
     */

    public function _toHtml()
    {
        if (!$this->getOptions()) {
            foreach ($this->_getProductAttributesList() as $attribute) {
                $this->addOption($attribute, $attribute);
            }
        }
        return parent::_toHtml();
    }
}
