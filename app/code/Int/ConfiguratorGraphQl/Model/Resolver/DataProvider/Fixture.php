<?php
/**
 * Indusnet Technologies.
 *
 * @category  Indusnet
 * @package   Int_ConfiguratorGraphQl
 * @author    Indusnet
 */
namespace Int\ConfiguratorGraphQl\Model\Resolver\DataProvider;

use Magento\Framework\GraphQl\Exception\GraphQlNoSuchEntityException;
use Magento\Framework\Exception\NoSuchEntityException;

class Fixture
{
    protected $_fixtureFactory;

    public function __construct(
        \Int\Configurator\Model\FixtureFactory $fixtureFactory
        )
    {
        $this->_fixtureFactory  = $fixtureFactory;
    }
    
    
    /**
     * Get fixture data
     * @return array
     * @throws GraphQlNoSuchEntityException
     */
    public function getFixturesData($fixtureId)
    {
        try {
            $fixture = $this->_fixtureFactory->create()->load($fixtureId);
            $fixtureData = $fixture->getData();

        } catch (NoSuchEntityException $e) {
            throw new GraphQlNoSuchEntityException(__($e->getMessage()), $e);
        }
        
        return $fixtureData;
    }
}
