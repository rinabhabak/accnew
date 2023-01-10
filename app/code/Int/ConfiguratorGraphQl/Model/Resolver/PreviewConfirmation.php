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
use Int\Configurator\Helper\Data as ConfiguratorHelper;
/**
 * Class PreviewConfirmation
 * @package Int\ConfiguratorGraphQl\Model\Resolver
 */
class PreviewConfirmation implements ResolverInterface
{
    
	protected $_configuratorHelper;
	protected $_configuratorProductHelper;
	
    public function __construct(
        ConfiguratorHelper $configuratorHelper,
		\Int\Configurator\Helper\Products $configuratorProductHelper
    ) {
        $this->_configuratorHelper = $configuratorHelper;
		$this->_configuratorProductHelper = $configuratorProductHelper;
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
				
		if(!isset($args['configurator_id']) || $args['configurator_id'] == '') {
			throw new GraphQlAuthorizationException(
                __('Attribute code and option id is required.')
            );
		}
		
		$products = $this->_configuratorHelper->getproductList($args['configurator_id']);
		$systems = $this->_configuratorProductHelper->getSelectedSystems($args['configurator_id']);
        foreach($systems as $key=>$system){
            $products['logo'][$key]['logo_url'] = $this->_configuratorProductHelper->getSystemLogoUrl($system);
        }
		return $products;
			
    }

}