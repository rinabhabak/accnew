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
use Magento\CustomerGraphQl\Model\Customer\GetCustomer;
use Magento\Framework\GraphQl\Exception\GraphQlAuthorizationException;


/**
 * Class GetCompanyUser
 * @package Int\CustomerGraphQl\Model\Resolver
 */
class GetCompanyUser implements ResolverInterface
{
   
    /**
* @var \Magento\Directory\Model\RegionFactory
*/
    protected $_regionFactory;
    protected $_customerFactory;
    /**
     * @var GetCustomer
     */
    private $getCustomer;
    protected $companyUserCollection;
    protected $helper;


     public function __construct(
        \Magento\Company\Api\CompanyManagementInterface $companyRepository,
        \Magento\Company\Block\Company\CompanyProfile $companyProfile,
        \Magento\Company\Model\ResourceModel\Users\Grid\CollectionFactory $companyUserCollection,
        \Magento\Company\Model\RoleManagement $role,
        \Int\CustomerGraphQl\Helper\Data $helper
    ) {
       $this->companyRepository = $companyRepository;
       $this->_companyProfile = $companyProfile;
       $this->companyUserCollection = $companyUserCollection;
       $this->_role = $role;
       $this->helper = $helper;
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

      /** @var ContextInterface $context */
        if (false === $context->getExtensionAttributes()->getIsCustomer()) {
            throw new GraphQlAuthorizationException(__('The current customer isn\'t authorized.'));
        }
            $customerId = $context->getUserId();
            $street = '';
            $company = $this->companyRepository->getByCustomerId($customerId);
            $companyId = $company->getEntityId();
            $userCollection = $this->companyUserCollection->create()->addFieldToFilter('company.entity_id', ['eq'=>$companyId]);
            $GetCompanyUser = [];
            if($userCollection->getSize() >0)
            {
                $i = 0;
                foreach($userCollection as $cUser)
                {
                    if($cUser->getRoleName() == null){
                        $companyRole = $this->_role->getCompanyAdminRoleName();
                    }
                    else{
                        $companyRole = $cUser->getRoleName();
                    }
                    $GetCompanyUser[$i]['id'] = $cUser->getEntityId();
                    $GetCompanyUser[$i]['name'] = $cUser->getName();
                    $GetCompanyUser[$i]['email'] = $cUser->getEmail();
                    $GetCompanyUser[$i]['role'] = $companyRole;
                    $GetCompanyUser[$i]['team'] = '';
                    $GetCompanyUser[$i]['status'] = $this->helper->setStatusLabel($cUser->getStatus());
                    $GetCompanyUser[$i]['status_code'] = $cUser->getStatus();

                    $i++;
                }
                
            }
            

        return $GetCompanyUser;

    }

   

}