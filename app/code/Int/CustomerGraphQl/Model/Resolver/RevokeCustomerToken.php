<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Int\CustomerGraphQl\Model\Resolver;

use Magento\Framework\Exception\AuthenticationException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlAuthenticationException;
use Magento\Framework\GraphQl\Exception\GraphQlAuthorizationException;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Integration\Api\CustomerTokenServiceInterface;
use Magento\Customer\Api\AccountManagementInterface;


class RevokeCustomerToken extends \Magento\CustomerGraphQl\Model\Resolver\RevokeCustomerToken
{
    /**
     * @var CustomerTokenServiceInterface
     */
    private $customerTokenService;
	private $oathCollection;
   

    /**
     * @param CustomerTokenServiceInterface $customerTokenService     
     */
    public function __construct(
        CustomerTokenServiceInterface $customerTokenService,
		\Magento\Integration\Model\Oauth\Token $oathCollection
    ) {
        $this->customerTokenService = $customerTokenService;
		$this->oathCollection = $oathCollection;
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
            throw new GraphQlAuthorizationException(__('Your session has been expired. Please log in again.'));
        }
		
		try {
			
				$oathCollection = $this->oathCollection->getCollection()
									   ->addFieldToFilter('customer_id', $context->getUserId())
									   ->addFieldToFilter('revoked', 0)
									   ->setPageSize(1)
									   ->setOrder('entity_id','DESC')->getData();
				$isRevoked = 0;
				foreach($oathCollection as $results) {
					$tokenObj = $this->oathCollection->load($results['entity_id']);
					$tokenObj->setRevoked(1);
					$tokenObj->save();
					$isRevoked = 1;
					
				}			
				return array('result' => 'Logout successful');
        } catch (AuthenticationException $e) {
            //throw new GraphQlAuthenticationException(__($e->getMessage()), $e);
			return array('result' => $e->getMessage());
        }
    }
    
    
    
}
