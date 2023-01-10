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

class Configurator
{
    protected $_configuratorFactory;

    public function __construct(
        \Int\Configurator\Model\ConfiguratorFactory $configuratorFactory
        )
    {
        $this->_configuratorFactory  = $configuratorFactory;
    }
    
    
    /**
     * Get configurator data
     * @return array
     * @throws GraphQlNoSuchEntityException
     */
    public function getConfiguratorData()
    {
        try {
            $collection = $this->_configuratorFactory->create()->getCollection();
            $collection->setOrder('created_at', 'DESC');
            $configuratorData = $collection->getData();

        } catch (NoSuchEntityException $e) {
            throw new GraphQlNoSuchEntityException(__($e->getMessage()), $e);
        }
        
        return $configuratorData;
    }
}
