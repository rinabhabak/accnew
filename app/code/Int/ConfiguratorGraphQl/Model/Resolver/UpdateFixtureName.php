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
 * Class UpdateFixtureName
 * @package Int\ConfiguratorGraphQl\Model\Resolver
 */
class UpdateFixtureName implements ResolverInterface
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
        if(!isset($args['input']['fixture_id'])){
            throw new \Exception('Fixture Id is require.');
        }


        if(!isset($args['input']['fixture_name'])){
            throw new \Exception('Fixture Name is require.');
        }


        try{

            $fixture = $this->_fixtureFactory->create();
            $fixture->load($args['input']['fixture_id']);
            if($fixture->getId()){
                $fixture->setFixtureName($args['input']['fixture_name'])->save();
                
                $_fixture = $fixture->load($fixture->getId());
                
                return $_fixture->getData();
            }else{
                throw new \Exception('Fixture not found.');
            }

        }catch (\Exception $e) {
            throw new \Exception(__($e->getMessage()));
        }
    }

}