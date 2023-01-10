<?php
/**
 * Indusnet Technologies.
 *
 * @category  Indusnet
 * @package   Int_CustomerGraphQl
 * @author    Indusnet
 */

namespace Int\CustomerGraphQl\Model\Resolver;

use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Exception\GraphQlNoSuchEntityException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Catalog\Api\Data\ProductInterface;

/**
 * Class getCustomerHistoryUpdates
 * @package Int\CustomerGraphQl\Model\Resolver
 */
class getCustomerHistoryUpdates implements ResolverInterface
{

    /**
     * @inheritdoc
     */
	protected $_statusFactory;
	
	public function __construct(
		\Int\CustomerHistoryUpdates\Model\StatusFactory $statusFactory
		)
	{
		$this->_statusFactory = $statusFactory;
	}
	
    public function resolve(
        Field $field,
        $context,
        ResolveInfo $info,
        array $value = null,
        array $args = null
    ) {        
	
		if (false === $context->getExtensionAttributes()->getIsCustomer()) {
            throw new GraphQlAuthorizationException(
                __('The current customer isn\'t authorized.')
            );
        }
		$_configuratorStatuses = $this->_statusFactory->create()->getCollection();	
		$_configuratorStatuses->getSelect()->joinLeft(
               ['configurator'=>$_configuratorStatuses->getTable('configurator')],
               'main_table.configurator_id = configurator.configurator_id',
               ['project_id'=>'configurator.project_id']);
		
		$_configuratorStatuses->getSelect()->where("main_table.customer_id=".$context->getUserId());
		$_configuratorStatuses->getSelect()->order('main_table.id DESC');
		
        $_configuratorStatuses = $_configuratorStatuses->getData();
        return $_configuratorStatuses;

    }

}