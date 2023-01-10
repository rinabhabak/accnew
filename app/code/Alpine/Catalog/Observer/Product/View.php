<?php
/**
 * Alpine_Catalog
 *
 * @category    Alpine
 * @package     Alpine_Catalog
 * @copyright   Copyright (c) 2018 Alpine Consulting, Inc
 * @author      Andrey Nesterov <andrey.nesterov@alpineinc.com>
 */
namespace Alpine\Catalog\Observer\Product;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\App\Request\Http;
use Magento\Framework\View\DesignInterface;

/**
 * Alpine\Catalog\Observer\Product\View
 *
 * @category    Alpine
 * @package     Alpine_Catalog
 */
class View implements ObserverInterface
{
    /**
     * Constant for customization theme
     *
     * @var string
     */
    const CUSTOMIZATION_THEME_CODE = 'Alpine/accuridecustomization';
        
    /**
     * Http request
     *
     * @var Http
     */
    protected $request;
    
    /**
     * Design
     *
     * @var DesignInterface
     */
    protected $design;

    /**
     * Constructor
     *
     * @param Http $request
     * @param DesignInterface $design
     */
    public function __construct(
        Http $request,
        DesignInterface $design
    ) {
        $this->request = $request;
        $this->design = $design;
    }

    /**
     * Execute
     *
     * @param Observer $observer
     * @return void
     */
    public function execute(Observer $observer)
    {
        $isCustomDesign = $this->request->getParam('b') === 'true';
        
        if ($isCustomDesign) {
            $this->design->setDesignTheme(self::CUSTOMIZATION_THEME_CODE);
        }
    }
}
