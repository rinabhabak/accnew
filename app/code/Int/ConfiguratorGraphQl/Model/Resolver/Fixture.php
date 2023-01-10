<?php
/**
 * Indusnet Technologies.
 *
 * @category  Indusnet
 * @package   Int_ConfiguratorGraphQl
 * @author    Indusnet
 */

namespace Int\ConfiguratorGraphQl\Model\Resolver;

use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Exception\GraphQlNoSuchEntityException;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Class Configurator
 * @package Int\ConfiguratorGraphQl\Model\Resolver
 */
class Fixture implements ResolverInterface
{

    private $_fixtureDataProvider;

    /**
     * @param Int\ConfiguratorGraphQl\Model\Resolver\DataProvider\Fixture $fixtureDataProvider
     */
    public function __construct(
        \Int\ConfiguratorGraphQl\Model\Resolver\DataProvider\Fixture $fixtureDataProvider
    ) {
        $this->_fixtureDataProvider = $fixtureDataProvider;
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

        $resultData =  $this->_fixtureDataProvider->getFixturesData($args['fixture_id']);
        return $resultData;
    }

}