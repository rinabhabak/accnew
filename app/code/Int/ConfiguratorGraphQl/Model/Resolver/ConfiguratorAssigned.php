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
use Int\Configurator\Model\BdmManagersFactory;
use Int\Configurator\Model\ConfiguratorFactory as ConfiguratorModel;

/**
 * Class ConfiguratorAssigned
 * @package Int\ConfiguratorGraphQl\Model\Resolver
 */
class ConfiguratorAssigned implements ResolverInterface
{
    /**
     * @var BdmManagersFactory
     */
    protected $_bdmManagersFactory;

    /**
     * @param BdmManagersFactory $bdmManagersFactory
     * @param ConfiguratorModel $configuratorFactory
     */
    public function __construct(
        BdmManagersFactory $bdmManagersFactory,
        ConfiguratorModel $configuratorFactory
    ) {
        $this->_bdmManagersFactory  = $bdmManagersFactory;
        $this->_configurator = $configuratorFactory;
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
                __('The current customer isn\'t authorized.')
            );
        }

        if(empty($args['input']['configurator_id'])){
            throw new GraphQlInputException(
                __('Configurator ID is Required.')
            );
        }
		
		if(empty($args['input']['assign_to'])){
            throw new GraphQlInputException(
                __('Assign to is Required.')
            );
        }
		
		if(empty($args['input']['assign_by'])){
            throw new GraphQlInputException(
                __('Assign by is Required.')
            );
        }
        $currentCustomerId = (int) $context->getUserId();
        $configuratorId = (int) $args['input']['configurator_id'];
        $assignTo = (int) $args['input']['assign_to'];
		$assignBy = (int) $args['input']['assign_by'];

        try{
            
            $_configurator = $this->_configurator->create()->load($configuratorId);
            if($_configurator->getId()){
				
                $assignBdmModel = $this->_bdmManagersFactory->create();
                $assignBdmModel->load($configuratorId, 'parent_id');
                $assignBdmModel->setParentId($configuratorId);
                $assignBdmModel->setAssignedTo($assignTo);
                $assignBdmModel->setAssignedBy($assignBy);

                if($assignBdmModel->save()){
                    if($_configurator->getBdsStatus()==1){
                        $_configurator->setBdsStatus(2)->save();
                    }
                }

                return [                   
                    "message" => __('Successfully Assigned.')
                ];
            }
			else
			{
				return [                    
                    "message" => __('Configurator Id is not valid.')
                ];
			}
            
        } catch (\Exception $e) {
            throw new GraphQlInputException(__($e->getMessage()));
        }
    }

}