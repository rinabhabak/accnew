<?php
namespace AddThis\FloatingShareBar\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;

class Data extends AbstractHelper
{
    const ENABLE = 'addthis_floatingsharebar/general/enable';
    const BLOCK_LABEL = 'addthis_floatingsharebar/general/block_label';
    const DESKTOP_POSITION  = 'addthis_floatingsharebar/general/desktop_position';
    const MOBILE_POSITION = 'addthis_floatingsharebar/general/mobile_position';
    const COUNTS = "addthis_floatingsharebar/general/counts";
    const NUM_PREFERRED_SERVICES = 'addthis_floatingsharebar/general/num_preferred_services';
    const STYLE = 'addthis_floatingsharebar/general/style';
    const MOBILE_BUTTON_SIZE = 'addthis_floatingsharebar/general/mobile_button_size';
    const THEME = 'addthis_floatingsharebar/general/theme';
     
    public function __construct(
        Context $context
    ) {
        parent::__construct($context);
    }

    public function getEnable()
    {
        return $this->scopeConfig->getValue(self::ENABLE);
    }

    public function getBlockLabel()
    {
        return $this->scopeConfig->getValue(self::BLOCK_LABEL);
    }

    public function getDesktopPosition()
    {
        return $this->scopeConfig->getValue(self::DESKTOP_POSITION);
    }

    public function getMobilePosition()
    {
        return $this->scopeConfig->getValue(self::MOBILE_POSITION);
    }

    public function getCounts()
    {
        return $this->scopeConfig->getValue(self::COUNTS);
    }

    public function getNumPreferredServices()
    {
        return $this->scopeConfig->getValue(self::NUM_PREFERRED_SERVICES);
    }

    public function getStyle()
    {
        return $this->scopeConfig->getValue(self::STYLE);
    }

    public function getMobileButtonSize()
    {
        return $this->scopeConfig->getValue(self::MOBILE_BUTTON_SIZE);
    }

    public function getTheme()
    {
        return $this->scopeConfig->getValue(self::THEME);
    }
}
