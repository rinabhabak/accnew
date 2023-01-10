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

/**
 * Class FixtureSave
 * @package Int\ConfiguratorGraphQl\Model\Resolver
 */
class FixtureSave implements ResolverInterface
{

    private $_fixtureDataProvider;
    protected $_fixtureFactory;
    protected $_fixture;
    protected $_configuratorFactory;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\TimezoneInterface
     */
	protected $timezone;

    /**
     * @param Int\ConfiguratorGraphQl\Model\Resolver\DataProvider\Fixture $fixtureDataProvider
     */
    public function __construct(
        \Int\ConfiguratorGraphQl\Model\Resolver\DataProvider\Fixture $fixtureDataProvider,
        \Int\Configurator\Model\FixtureFactory $fixtureFactory,
        \Int\Configurator\Model\Fixture $fixture,
        \Int\Configurator\Model\ConfiguratorFactory $configuratorFactory,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $timezone
    ) {
        $this->_fixtureFactory  = $fixtureFactory;
        $this->_fixture  = $fixture;
        $this->_configuratorFactory  = $configuratorFactory;
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
        if (false === $context->getExtensionAttributes()->getIsCustomer()) {
            throw new GraphQlAuthorizationException(
                __('Your session has been expired. Please log in again.')
            );
        }
        
        try{
        
            $fixture = $this->_fixtureFactory->create();
            if(isset($args['input']['fixture_id'])){
                $fixture->load($args['input']['fixture_id']);
            }
            if(!$fixture->getId()){
                $args['input']['created_at'] = $this->timezone->date()->format('Y-m-d H:i:s');
            }
            
            $args['input']['updated_at'] = $this->timezone->date()->format('Y-m-d H:i:s');
            //$args['input']['status'] = 1;
            
            $fixture->setData($args['input'])->save();
			
			$configurator = $this->_configuratorFactory->create();
			if(isset($args['input']['configurator_id'])){
                $configurator = $configurator->load($args['input']['configurator_id']);                
                $sameFixtureDimensions = $configurator->getSameFixtureDimensions();
				if($sameFixtureDimensions  == 1) {
					$fixturesCollection = $this->_fixtureFactory->create()->getCollection()->addFieldToFilter('configurator_id',$args['input']['configurator_id'])->getData();
					foreach($fixturesCollection as $fixtureresults) {					
						$fixtureObj = $this->_fixtureFactory->create()->load($fixtureresults['fixture_id']);
						$args['input']['fixture_id']=$fixtureresults['fixture_id'];
						$fixtureObj->setData($args['input'])->save();
					}
				}
            }
			
			
            $_fixture = $fixture->load($fixture->getId());
            return $_fixture->getData();
        }catch(\Exception $e){
            throw new \Exception(__($e->getMessage()));
        }
    }

}