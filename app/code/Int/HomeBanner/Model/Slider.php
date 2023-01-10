<?php
/**
 * Indusnet Technologies.
 *
 * @category  Indusnet
 * @package   Int_HomeBanner
 * @author    Indusnet
 */
namespace Int\HomeBanner\Model;
 
use \Magento\Framework\Model\AbstractModel;
 
class Slider extends AbstractModel
{
    const BANNER_ID = 'slider_id'; // We define the id fieldname
 
    /**
     * Prefix of model events names
     *
     * @var string
     */
    protected $_eventPrefix = 'crud'; // parent value is 'core_abstract'
 
    /**
     * Name of the event object
     *
     * @var string
     */
    protected $_eventObject = 'slider'; // parent value is 'object'
 
    /**
     * Name of object id field
     *
     * @var string
     */
    protected $_idFieldName = self::BANNER_ID; // parent value is 'id'
 
    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Int\HomeBanner\Model\ResourceModel\Slider');
    }
 
}
