<?php
/**
 * Alpine_Catalog
 *
 * @category    Alpine
 * @package     Alpine_Catalog
 * @copyright   Copyright (c) 2018 Alpine Consulting, Inc
 * @author      Aleksandr Mikhailov (aleksandr.mikhailov@alpineinc.com)
 */

namespace Alpine\Catalog\Block\Product\ProductList;

use Alpine\Catalog\Model\Product\ProductList\AvailableProductsToolbar as AvailableProductsToolbarModel;
use Magento\Catalog\Block\Product\ProductList\Toolbar;
use Magento\Catalog\Helper\Product\ProductList;
use Magento\Catalog\Model\Config;
use Magento\Catalog\Model\Session;
use Magento\Framework\Data\Helper\PostHelper;
use Magento\Framework\Url\EncoderInterface;
use Magento\Framework\View\Element\Template\Context;

/**
 * Class OtherProductsToolbar
 *
 * @category    Alpine
 * @package     Alpine_Catalog
 */
class AvailableProductsToolbar extends Toolbar
{
    /**
     * Path to template file in theme.
     *
     * @var string
     */
    protected $_template = 'Magento_Catalog::product/list/toolbar.phtml';

    /**
     * AvailableProductsToolbar constructor
     *
     * @param Context                       $context
     * @param Session                       $catalogSession
     * @param Config                        $catalogConfig
     * @param AvailableProductsToolbarModel $toolbarModel
     * @param EncoderInterface              $urlEncoder
     * @param ProductList                   $productListHelper
     * @param PostHelper                    $postDataHelper
     * @param array                         $data
     */
    public function __construct(
        Context $context,
        Session $catalogSession,
        Config $catalogConfig,
        AvailableProductsToolbarModel $toolbarModel,
        EncoderInterface $urlEncoder,
        ProductList $productListHelper,
        PostHelper $postDataHelper,
        array $data = []
    ) {
        parent::__construct(
            $context,
            $catalogSession,
            $catalogConfig,
            $toolbarModel,
            $urlEncoder,
            $productListHelper,
            $postDataHelper,
            $data
        );
    }
}
