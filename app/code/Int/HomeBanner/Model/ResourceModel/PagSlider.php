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
 * PagSlider post mysql resource
 */
class PagSlider extends AbstractDb
{
 
    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        // Table Name and Primary Key column
        $this->_init('int_home_pagination_slider', 'slider_id');
    }
 
}
