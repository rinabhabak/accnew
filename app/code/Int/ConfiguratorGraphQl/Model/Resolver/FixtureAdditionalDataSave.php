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
class FixtureAdditionalDataSave implements ResolverInterface
{

    private $_fixtureDataProvider;
    protected $_fixtureFactory;
    protected $_fixture;

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
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $timezone
    ) {
        $this->_fixtureFactory  = $fixtureFactory;
        $this->_fixture  = $fixture;
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
        $_response = array();
        $fixture = $this->_fixtureFactory->create();
        if(isset($args['input']['fixture_id'])){
            $fixture->load($args['input']['fixture_id']);
            
            $args['input']['updated_at'] = $this->timezone->date()->format('Y-m-d H:i:s');
            
            $fixture->setAdditionalData(serialize($args['input']['additional_data']))
                        ->setStatus(\Int\Configurator\Model\Fixture::STATUS_COMPLETE)
                        ->save();
            
            $_fixture = $fixture->load($fixture->getId());
            $_response['fixture_id'] = $_fixture->getId();
            $_response['additional_data'] = unserialize($_fixture->getAdditionalData());
        
            return $_response;
        }
        return false;
    }

}