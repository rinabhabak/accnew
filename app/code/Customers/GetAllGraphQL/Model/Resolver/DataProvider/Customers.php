<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Customers\GetAllGraphQL\Model\Resolver\DataProvider;

class Customers
{

    protected $_customerFactory;

    /**
     * @param \Magento\Customer\Model\CustomerFactory $customerFactory
     */
    public function __construct(
       \Magento\Customer\Model\CustomerFactory $customerFactory
    ) {
        $this->_customerFactory = $customerFactory;
    }

    public function getCustomers()
    {
    	$customers = $this->_customerFactory->create()->getCollection()
                  ->addAttributeToSelect("*"); 
        
        $allCustomers = [];             

		foreach($customers as $k => $customer)
		{ 
			$allCustomers['getlist'][$k]['id'] = $customer->getEntityId();
            $allCustomers['getlist'][$k]['website_id'] = $customer->getWebsiteId();
            $allCustomers['getlist'][$k]['group_id'] = $customer->getGroupId();
            $allCustomers['getlist'][$k]['store_id'] = $customer->getStoreId();
            $allCustomers['getlist'][$k]['email'] = $customer->getEmail();
            $allCustomers['getlist'][$k]['firstname'] = $customer->getFirstname();
            $allCustomers['getlist'][$k]['lastname'] = $customer->getLastname();
		}
        return $allCustomers;
    }
}

