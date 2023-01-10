<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Feed
 */


namespace Amasty\Feed\Controller\Adminhtml\Field;

use Amasty\Feed\Controller\Adminhtml\AbstractField;
use Magento\Framework\Controller\ResultFactory;

/**
 * Class Index
 *
 * @package Amasty\Feed
 */
class Index extends AbstractField
{
    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultFactory->create(ResultFactory::TYPE_PAGE);
        $resultPage->setActiveMenu('Amasty_Feed::feed_field');
        $resultPage->addBreadcrumb(__('Amasty Feed'), __('Amasty Feed'));
        $resultPage->addBreadcrumb(__('Custom Fields'), __('Custom Fields'));
        $resultPage->getConfig()->getTitle()->prepend(__('Custom Fields'));

        return $resultPage;
    }
}
