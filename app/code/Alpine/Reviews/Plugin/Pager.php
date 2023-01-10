<?php
/**
 * Alpine_Reviews
 *
 * @category    Alpine
 * @package     Alpine_Reviews
 * @copyright   Copyright (c) 2018 Alpine Consulting, Inc (www.alpineinc.com)
 * @author      Lev Zamansky <lev.zamanskiy@alpineinc.com>
 * @author      Evgeniy Derevyanko <evgeniy.derevyanko@alpineinc.com>
 */
namespace Alpine\Reviews\Plugin;

/**
 * Pager
 *
 * @category    Alpine
 * @package     Alpine_Reviews
 */
class Pager
{
    /**
     * Unset collection page size plugin method
     * 
     * @var \Magento\Review\Block\Product\View\ListView $subject
     * @var \Magento\Review\Model\ResourceModel\Review\Collection $result
     * @return \Magento\Review\Model\ResourceModel\Review\Collection
     */
    public function afterGetReviewsCollection($subject, $result)
    {
        $result->setPageSize(false);
        return $result;
    }
}
