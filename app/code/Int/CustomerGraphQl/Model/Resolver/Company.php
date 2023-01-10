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
 * Class Company
 * @package Int\CustomerGraphQl\Model\Resolver
 */
class Company implements ResolverInterface
{
    public function __construct(
        \Magento\Company\Api\CompanyRepositoryInterface $companyRepository
    ) {
        $this->companyRepository = $companyRepository;
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

        $companyName = '';
        if(isset($value['extension_attributes']['company_attributes'])){
            if(isset($value['extension_attributes']['company_attributes']['company_id']) && ($value['extension_attributes']['company_attributes']['company_id'] >0)){
             $companyId = $value['extension_attributes']['company_attributes']['company_id'];
             $company = $this->companyRepository->get($companyId);
             $companyName = $company->getCompanyName();
            }
        }
       
       return $companyName;

    }

}