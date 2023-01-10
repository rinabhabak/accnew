<?php
/**
 * Magedelight
 *
 * @category Magedelight
 * @package  Magedelight_Cybersourcesop
 * @author   Magedelight <info@magedelight.com>
 * @license  http://opensource.org/licenses/gpl-3.0.html GNU General Public License,version 3 (GPL-3.0)
 * @copyright Copyright (c) 2021 Magedelight (http://www.magedelight.com)
 */
namespace Magedelight\Cybersourcesop\Controller\SilentOrder;

use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Action\Action;
/**
 * Class SuccessResponse
 * @package Magedelight\Cybersourcesop\Controller\SilentOrder
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class SuccessResponse extends Action
{
    /**
     * 
     * @param Context $context
     */
    public function __construct(
        Context $context
    ) {
        parent::__construct($context);
    }

    public function execute()
    {
         
    }
}
