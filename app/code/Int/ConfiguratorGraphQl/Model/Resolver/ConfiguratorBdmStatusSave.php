<?php
/**
 * Indusnet Technologies.
 *
 * @category  Indusnet
 * @package   Int_ConfiguratorGraphQl
 * @author    Indusnet
 */

namespace Int\ConfiguratorGraphQl\Model\Resolver;

//resolver section
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Exception\GraphQlNoSuchEntityException;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Framework\GraphQl\Exception\GraphQlAuthorizationException;

/**
 * Class ConfiguratorBdmStatusSave
 * @package Int\ConfiguratorGraphQl\Model\Resolver
 */
class ConfiguratorBdmStatusSave implements ResolverInterface
{

    
    protected $_configuratorFactory;
    protected $_configurator;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\TimezoneInterface
     */
	protected $timezone;
    /**
     * @param Int\ConfiguratorGraphQl\Model\Resolver\DataProvider\Configurator $configuratorDataProvider
     */
    public function __construct(       
        \Int\Configurator\Model\ConfiguratorFactory $configuratorFactory,
        \Int\Configurator\Model\Configurator $configurator,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $timezone
    ) {
        $this->_configuratorFactory  = $configuratorFactory;
        $this->_configurator  = $configurator;
        $this->timezone = $timezone;
    }

    /**
     * @inheritdoc
     */
    public function resolve(
        Field $field,
        $context,
        ResolveInfo $info,
        array $value = null,
        array $args = null
    ) {
        

		$_configuratorData  = array(); 
	    if(!isset($args['input']['configurator_id'])){
		    throw new GraphQlInputException(__('configurator_id is required.'));
	    }
	    if(!isset($args['input']['bds_status'])){
			throw new GraphQlInputException(__('bds_status is required.'));
	    }
	    	   
        try{                     
            $configurator = $this->_configuratorFactory->create();
            if(isset($args['input']['configurator_id'])){
                $configurator = $configurator->load($args['input']['configurator_id']);
				if($configurator->getId()) {		
					$configurator->setBdsStatus($args['input']['bds_status']);
                    if($args['input']['bds_status']==4){
                        $configurator->setBdsCompletedDate($this->timezone->date()->format('Y-m-d H:i:s'));
                        $configurator->setUpdatedAt($this->timezone->date()->format('Y-m-d H:i:s'));
                    }else{
                        $configurator->setBdsCompletedDate(NULL);
                        $configurator->setUpdatedAt($this->timezone->date()->format('Y-m-d H:i:s'));
                    }
                    
					$configurator->save();
                    
					$_configuratorData['configurator_id'] = $args['input']['configurator_id'];
					$_configuratorData['bds_status'] = $args['input']['bds_status'];
				}
            }
			
            return $_configuratorData;
            
        } catch (\Exception $e) {
            throw new \Exception(__($e->getMessage()));
        }
    }
}