<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Stockstatus
 */


namespace Amasty\Stockstatus\Plugin\Catalog\Block\Product;

class ListProductPlugin
{
    /**
     * @var bool
     */
    private $isEnabled = null;

    /**
     * @var \Amasty\Stockstatus\Helper\Data
     */
    private $helper;

    public function __construct(
        \Amasty\Stockstatus\Helper\Data $helper
    ) {
        $this->helper = $helper;
    }

    /**
     * @param $subject
     * @param \Closure $proceed
     * @param \Magento\Catalog\Model\Product $product
     *
     * @return mixed|string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function aroundGetProductDetailsHtml(
        $subject,
        \Closure $proceed,
        \Magento\Catalog\Model\Product $product
    ) {
        $html = $proceed($product);
        if ($this->isEnabledOnCategory()) {
            $status = $this->helper->showStockStatus($product, true, true);
            if ($status != '') {
                $status = sprintf(
                    '<div class="amstockstatus-category">%s</div>',
                    $status . $this->helper->getInfoBlock()
                );
            }

            $html .= $status;
        }

        return $html;
    }

    /**
     * @param $subject
     * @param string $result
     *
     * @return string
     */
    public function afterToHtml(
        $subject,
        $result
    ) {
        if ($this->isEnabledOnCategory()) {
            $result .= '
                <script type="text/javascript">
                    require([
                        "jquery"
                    ], function($) {
                        $(".amstockstatus").each(function(i, item) {
                            var parent = $(item).parents(".item").first();
                            parent.find(".actions .stock").remove();
                        })
                    });
                </script>
            ';
        }

        return $result;
    }

    /**
     * @return bool
     */
    protected function isEnabledOnCategory()
    {
        if ($this->isEnabled === null) {
            $this->isEnabled = (bool)$this->helper->getModuleConfig('display/display_on_category');
        }

        return $this->isEnabled;
    }
}
