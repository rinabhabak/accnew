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
 * Class GetJobTitle
 * @package Int\CustomerGraphQl\Model\Resolver
 */
class GetJobTitle implements ResolverInterface
{

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


        $jobTitle = '';

        if(isset($value['extension_attributes']['company_attributes'])){
        	if(isset($value['extension_attributes']['company_attributes']['job_title'])){
            	$jobTitle = $value['extension_attributes']['company_attributes']['job_title'];
         	}
        }
       
       return $jobTitle;

    }

}