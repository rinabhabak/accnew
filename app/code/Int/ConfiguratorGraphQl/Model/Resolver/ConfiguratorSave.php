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
 * Class ConfiguratorSave
 * @package Int\ConfiguratorGraphQl\Model\Resolver
 */
class ConfiguratorSave implements ResolverInterface
{

    private $_configuratorDataProvider;
    protected $_configuratorFactory;
    protected $_configurator;
    protected $_fixtureFactory;
    protected $_statusFactory;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\TimezoneInterface
     */
    protected $timezone;

    /**
     * @param Int\ConfiguratorGraphQl\Model\Resolver\DataProvider\Configurator $configuratorDataProvider
     */
    public function __construct(
        \Int\ConfiguratorGraphQl\Model\Resolver\DataProvider\Configurator $configuratorDataProvider,
        \Int\Configurator\Model\ConfiguratorFactory $configuratorFactory,
        \Int\Configurator\Model\Configurator $configurator,
        \Int\Configurator\Model\FixtureFactory $fixtureFactory,
        \Int\CustomerHistoryUpdates\Model\StatusFactory $statusFactory,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $timezone
    ) {
        $this->_configuratorFactory  = $configuratorFactory;
        $this->_configurator  = $configurator;
        $this->_fixtureFactory  = $fixtureFactory;
        $this->_statusFactory = $statusFactory;
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
            $_configuratorData  = array();
            $existingFixtures = 0;
            
            if(isset($args['input']['customer_id'])) {
                $args['input']['customer_id'] = base64_decode($args['input']['customer_id']);
            }
            
            $customerId = isset($args['input']['customer_id']) ? $args['input']['customer_id']:$context->getUserId();           
            $args['input']['customer_id'] = $customerId;
            
            $configurator = $this->_configuratorFactory->create();
            if(isset($args['input']['configurator_id'])){
                $configurator = $configurator->load($args['input']['configurator_id']);
                
                $existingFixtures = $configurator->getNumbersOfFixture();
            }
            else{
                $args['input']['bds_status'] = 1;
            }
            
            if(!$configurator->getId()){
                $args['input']['created_at'] = $this->timezone->date()->format('Y-m-d H:i:s');
            }
            $nof = isset($args['input']['numbers_of_fixture'])?$args['input']['numbers_of_fixture']:0;
            
            $args['input']['updated_at'] = $this->timezone->date()->format('Y-m-d H:i:s');
            $args['input']['status'] = 1;
            
            /*
            if($nof >= 15 ) {
                $args['input']['is_consulatative_sale'] = 1;
            }
            else{
                $args['input']['is_consulatative_sale'] = 0;
            }
            */
            
            $configurator->setData($args['input'])->save();
            
            if($nof && $configurator->getId()){

                if(!isset($args['input']['configurator_id'])){

                    for ($i=1; $i<=$nof; $i++ ) {                          
                        $fixture = $this->_fixtureFactory->create();     
                        $fixtureArgs['fixture_name'] = __('Fixture %1',$i);
                        $fixtureArgs['created_at'] = $this->timezone->date()->format('Y-m-d H:i:s');                
                        $fixtureArgs['updated_at'] = $this->timezone->date()->format('Y-m-d H:i:s');
                        $fixtureArgs['configurator_id'] = $configurator->getId();
                        $fixtureArgs['status'] = \Int\Configurator\Model\Fixture::STATUS_PENDING;
                        $fixture->setData($fixtureArgs)->save();

                    }

                }else{

                    if($nof > $existingFixtures){
                        //$_nofs = ($nof - $existingFixtures);
                        $j=$existingFixtures+1;
                        for ($i=$j; $i<=$nof; $i++ ) {  

                            $fixture = $this->_fixtureFactory->create();     
                            $fixtureArgs['fixture_name'] = __('Fixture %1',$i);
                            $fixtureArgs['created_at'] = $this->timezone->date()->format('Y-m-d H:i:s');                
                            $fixtureArgs['updated_at'] = $this->timezone->date()->format('Y-m-d H:i:s');
                            $fixtureArgs['configurator_id'] = $configurator->getId();
                            $fixtureArgs['status'] = \Int\Configurator\Model\Fixture::STATUS_PENDING;
                            $fixture->setData($fixtureArgs)->save();
                        }

                    }elseif($nof < $existingFixtures){

                        $fixtures = $this->_fixtureFactory->create()->getCollection();

                        $fixtures->addFieldToFilter('configurator_id',$configurator->getId());
                        //$totalFixture = count($fixtures->getAllIds());
                        $totalDelete = $existingFixtures-$nof;

                        if($totalDelete > 0){
                            $fixtures->addFieldToFilter('configurator_id',$configurator->getId());
                            $fixtures->setOrder(
                                'fixture_id',
                                'desc'
                            )->setPageSize($totalDelete)->setCurPage(1);

                            foreach ($fixtures as $fixture) {
                                $fixture->delete();
                            }
                        }


                    }
                }
            }

            // save project id
            if(!isset($args['input']['configurator_id'])){
                $randomNo = $this->random_strings();
                $projectId =  $randomNo;
                $_configurator = $configurator->load($configurator->getId());            
                $_configurator->setProjectId($projectId);
                $_configurator->save();     
            }
            
            $_configurator = $configurator->load($configurator->getId());
            $_configuratorData = $_configurator->getData();

            
            
            // Update configurator status history
            if($configurator->getId()){
                $customerHistoryStatus = $this->_statusFactory->create();
                $customerHistoryStatus->setCustomerId($customerId);
                $customerHistoryStatus->setStatus('1');
                if(isset($args['input']['configurator_id'])){
                    $customerHistoryStatus->setMessage(__('Your project has been updated #%1',$configurator->getProjectId()));
                    $customerHistoryStatus->setUpdatedAt($this->timezone->date()->format('Y-m-d H:i:s'));
                }else {
                    $customerHistoryStatus->setMessage(__('We received your request #%1',$configurator->getProjectId()));
                    $customerHistoryStatus->setCreatedAt($this->timezone->date()->format('Y-m-d H:i:s'));
                    $customerHistoryStatus->setUpdatedAt($this->timezone->date()->format('Y-m-d H:i:s'));
                }
                
                $customerHistoryStatus->setConfiguratorId($configurator->getId());
                $customerHistoryStatus->save();
            }
            
            
            
            if($configurator->getId()){
                $fixtures = $this->_fixtureFactory->create()->getCollection();
                $fixtures->addFieldToFilter('configurator_id',$configurator->getId());
                $totalFixture = count($fixtures->getAllIds());
                    $totalDelete = $totalFixture-$nof;

                $_configuratorData['fixtures'] = $fixtures->getData(); 
            }

            $_configuratorData['customer_id'] = base64_encode($configurator->getCustomerId());
            return $_configuratorData;

        } catch (\Exception $e) {
            throw new \Exception(__($e->getMessage()));
        }
                
        
    }
    
    public function random_strings() 
    {     
        // String of all alphanumeric character 
        $str_characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ'; 
        $str_digits = '0123456789'; 
        
        $characterString = substr(str_shuffle($str_characters), 0, 4);
        $digits = substr(str_shuffle($str_digits), 0, 6); 
        $projectId = $characterString.$digits;
        //$projectId = 'APKQ960845';
        if($this->checkProjectId($projectId)) {
            
            $this->random_strings();
        }
        else{
            
            return $projectId;
        }
    }
    
    public function checkProjectId($projectId) {
        $configurator = $this->_configuratorFactory->create()->getCollection()->addFieldToFilter('project_id',$projectId);
        
        if(count($configurator->getAllIds()) >= 1){
            
            return true;
        }
        else{
            
            return false;
        }
    }

}
