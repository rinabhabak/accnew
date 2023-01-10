<?php
/**
 * Quote index controller
 *
 * @category    Alpine
 * @package     Alpine_Cms
 * @copyright   Copyright (c) 2018 Alpine Consulting Inc.
 * @author      Dmitry Naumov <dmitry.naumov@alpineinc.com>
 */

namespace Alpine\Cms\Controller\Index;

use Alpine\Cms\Controller\Index as ControllerIndex;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Controller\ResultInterface;

/**
 * Quote index controller
 *
 * @category    Alpine
 * @package     Alpine_Cms
 */
class Index extends ControllerIndex
{
    /**
     * Show quote form page
     *
     * @return ResultInterface
     */
    public function execute()
    {
        return $this->resultFactory->create(ResultFactory::TYPE_PAGE);
    }
}