<?php
/**
 * Source for config field / Alpine_SLIFeed
 *
 * @copyright   Copyright (c) 2019 Alpine Consulting, Inc
 * @author      Evgeniy Derevyanko <evgeniy.derevyanko@alpineinc.com>
 */

namespace Alpine\SLIFeed\Model\Source;

use Magento\Cms\Model\ResourceModel\Page\CollectionFactory;

/**
 * Class PagesSelect
 */
class PagesSelect implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * @var CollectionFactory
     */
    protected $pageCollectionFactory;

    /**
     * PagesSelect constructor.
     *
     * @param CollectionFactory $pageCollectionFactory
     */
    public function __construct(
        CollectionFactory $pageCollectionFactory
    ) {
        $this->pageCollectionFactory = $pageCollectionFactory;
    }

    /**
     * Returns array of CMS pages
     *
     * @return array
     */
    public function toOptionArray()
    {
        $pageCollection = $this->pageCollectionFactory->create()
            ->addFieldToSelect('title')
            ->addFieldToSelect('identifier');

        $result = [];

        foreach ($pageCollection as $cmsPage) {
            $result[] = [
                'value' => $cmsPage->getIdentifier(),
                'label' => __($cmsPage->getTitle())
            ];
        }

        return $result;
    }
}
