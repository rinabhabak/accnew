<?php
/**
 * Indusnet Technologies.
 *
 * @category  Indusnet
 * @package   Int_HomeBanner
 * @author    Indusnet
 */
namespace Int\HomeBanner\Model\ResourceModel\Thumbnail;
 
use \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
 
class Collection extends AbstractCollection
{
 
    protected $_idFieldName = \Int\HomeBanner\Model\Thumbnail::BANNER_ID;
     
    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Int\HomeBanner\Model\Thumbnail', 'Int\HomeBanner\Model\ResourceModel\Thumbnail');
    }
 
}
