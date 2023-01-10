<?php
/**
 * Alpine_Acton
 *
 * @category    Alpine
 * @package     Alpine_Acton
 * @copyright   Copyright (c) 2018 Alpine Consulting, Inc
 * @author      Andrey Nesterov <andrey.nesterov@alpineinc.com>
 */

namespace Alpine\Acton\Observer\Webforms\Model;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\App\RequestInterface;

/**
 * Alpine\Acton\Observer\Webforms\Model\Form
 *
 * @category    Alpine
 * @package     Alpine_Acton
 */
class Form implements ObserverInterface
{
    /**
     * Request
     *
     * @var RequestInterface
     */
    protected $request;

    /**
     * Constructor
     *
     * @param RequestInterface $request
     */
    public function __construct(
        RequestInterface $request
    ) {
        $this->request = $request;
    }

    /**
     * Execute
     *
     * @param Observer $observer
     * @return void
     */
    public function execute(Observer $observer)
    {
        $result = $observer->getData('result');
        
        if ($result) {
            $this->request->setParams(
                [
                    'result_id' => $result->getId()
                ]
            );
        }
    }
}
