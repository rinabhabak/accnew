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
 * Class ConfiguratorDataAssigned
 * @package Int\ConfiguratorGraphQl\Model\Resolver
 */
class ConfiguratorDataAssigned implements ResolverInterface
{
    /**
     * @var BdmManagersFactory
     */
    protected $_bdmManagersFactory;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\TimezoneInterface
     */
    protected $timezone;

    /**
     * @param BdmManagersFactory $bdmManagersFactory
     * @param ConfiguratorModel $configuratorFactory
     */
    public function __construct(
        BdmManagersFactory $bdmManagersFactory,
        ConfiguratorModel $configuratorFactory,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $timezone
    ) {
        $this->_bdmManagersFactory  = $bdmManagersFactory;
        $this->_configurator = $configuratorFactory;
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

        if(empty($args['input']['configurator_id']) || empty($args['input']['bdm_manager_id'])){
            throw new GraphQlInputException(
                __('Configurator ID and BDM Manager ID is Required.')
            );
        }

        $currentCustomerId = (int) $context->getUserId();
        $configuratorId = (int) $args['input']['configurator_id'];
        $bdmManagerId = (int) $args['input']['bdm_manager_id'];


        try{
            
            $_configurator = $this->_configurator->create()->load($configuratorId);

            if($_configurator->getId()){

                //$_configurator->setStatus(\Int\Configurator\Model\Status::STATUS_INPROCESS)->save();
                $_configurator->setBdsStatus(2)->save();
                
                $assignBdmModel = $this->_bdmManagersFactory->create();

                $assignBdmModel->load($configuratorId, 'parent_id');

                if($assignBdmModel->getId()){
                    throw new GraphQlInputException(
                        __('Configurator Already Assigned')
                    );
                }

                $assignBdmModel->setParentId($configuratorId);
                $assignBdmModel->setAssignedTo($bdmManagerId);
                $assignBdmModel->setAssignedBy($currentCustomerId);
                $assignBdmModel->setCreatedAt($this->timezone->date()->format('Y-m-d H:i:s'));
                $assignBdmModel->setUpdatedAt($this->timezone->date()->format('Y-m-d H:i:s'));
                $assignBdmModel->save();

                return [
                    "assigned_id" => $assignBdmModel->getId(),
                    "message" => __('BDM Manager Successfully Assigned.')
                ];
            }
            
        } catch (\Exception $e) {
            throw new GraphQlInputException(__($e->getMessage()));
        }
    }

}