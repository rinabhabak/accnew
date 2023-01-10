<?php
/**
 * Indusnet Technologies.
 *
 * @category  Indusnet
 * @package   Int_HomeBanner
 * @author    Indusnet
 */
namespace Int\HomeBanner\Model\ResourceModel;
 
use \Magento\Framework\Model\ResourceModel\Db\AbstractDb;
 
/**
 * SecondSlider post mysql resource
 */
class SecondSlider extends AbstractDb
{
 
    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        // Table Name and Primary Key column
        $this->_init('int_home_second_banner_slider', 'slider_id');
    }
 
}
