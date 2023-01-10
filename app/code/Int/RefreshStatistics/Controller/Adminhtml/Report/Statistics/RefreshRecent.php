<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Int\RefreshStatistics\Controller\Adminhtml\Report\Statistics;

use Magento\Framework\App\Action\HttpPostActionInterface as HttpPostActionInterface;
use Magento\Framework\Controller\ResultFactory; 

/**
 * Refresh recent stats.
 */
class RefreshRecent extends \Magento\Reports\Controller\Adminhtml\Report\Statistics\RefreshRecent
{
    /**
     * Refresh statistics for last 25 hours
     *
     * @return void
     */
    public function execute()
    {
        try {
            $collectionsNames = $this->_getCollectionNames();
            /** @var \DateTime $currentDate */
            $currentDate = $this->_objectManager->get(
                \Magento\Framework\Stdlib\DateTime\TimezoneInterface::class
            )->date();
            $date = $currentDate->modify('-25 hours');
            foreach ($collectionsNames as $collectionName) {
                $this->_objectManager->create($collectionName)->aggregate($date);
            }
            $this->messageManager->addSuccess(__('Recent statistics have been updated.'));
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $this->messageManager->addError($e->getMessage());
        } catch (\Exception $e) {
            $this->messageManager->addError(__('We can\'t refresh recent statistics.'));
            $this->_objectManager->get(\Psr\Log\LoggerInterface::class)->critical($e);
        }
        if ($this->_getSession()->isFirstPageAfterLogin()) {
            $this->_redirect('adminhtml/*');
        } else {
            $this->getResponse()->setRedirect($_SERVER["HTTP_REFERER"]);
            //$this->getResponse()->setRedirect($this->_redirect->getRedirectUrl('*/*'));
        }
    }
}
