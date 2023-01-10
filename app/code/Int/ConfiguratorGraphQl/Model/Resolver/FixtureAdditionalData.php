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
class FixtureAdditionalData implements ResolverInterface
{

    private $_fixtureDataProvider;
    protected $_fixtureFactory;
    protected $_fixture;

    /**
     * @param Int\ConfiguratorGraphQl\Model\Resolver\DataProvider\Fixture $fixtureDataProvider
     */
    public function __construct(
        \Int\ConfiguratorGraphQl\Model\Resolver\DataProvider\Fixture $fixtureDataProvider,
        \Int\Configurator\Model\FixtureFactory $fixtureFactory,
        \Int\Configurator\Model\Fixture $fixture
    ) {
        $this->_fixtureFactory  = $fixtureFactory;
        $this->_fixture  = $fixture;
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
            $fixtureId = $args['input']['fixture_id'];
            $_fixture = $fixture->load($fixtureId);
            $_response['fixture_id'] = $_fixture->getId();
            $_response['additional_data'] = unserialize($_fixture->getAdditionalData());
        
            return $_response;
        }else{
            throw new \Exception('Invalid Fixture Id.');
        }
    }

}