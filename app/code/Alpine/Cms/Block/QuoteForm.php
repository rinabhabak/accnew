<?php
/**
 * Quote form
 *
 * @category    Alpine
 * @package     Alpine_Cms
 * @copyright   Copyright (c) 2018 Alpine Consulting Inc.
 * @author      Dmitry Naumov <dmitry.naumov@alpineinc.com>
 */

namespace Alpine\Cms\Block;

use Magento\Contact\Block\ContactForm;
use Magento\Directory\Model\RegionFactory;
use Magento\Framework\View\Element\Template\Context;

/**
 * Quote form block
 *
 * @category    Alpine
 * @package     Alpine_Cms
 */
class QuoteForm extends ContactForm
{
    /**
     * Region factory collection
     *
     * @var RegionFactory $regionColFactory
     */
    protected $regionColFactory;

    /**
     * QuoteForm constructor
     *
     * @param Context $context
     * @param array $data
     * @param RegionFactory $regionColFactory
     */
    public function __construct(Context $context, RegionFactory $regionColFactory, array $data = [])
    {
        parent::__construct($context, $data);
        $this->regionColFactory = $regionColFactory;
    }

    /**
     * Returns action url for quote form
     *
     * @return string
     */
    public function getFormAction()
    {
        return $this->getUrl('quote/index/post', ['_secure' => true]);
    }

    /**
     * Returns regions options
     *
     * @return string
     */
    public function getRegionsOptions()
    {
        $regions = $this->regionColFactory
            ->create()
            ->getCollection()
            ->addFieldToFilter('country_id', 'US');
        $result = [];
        if (count($regions) > 0) {
            $result = ['STATE'];
            foreach ($regions as $state) {
                $result [] = $state->getName();
            }
        }
        return $result;
    }
}