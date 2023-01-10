<?php

/* Alpine_Acton
 *
 * @category    Alpine
 * @package     Alpine_Acton
 * @copyright   Copyright (c) 2018 Alpine Consulting, Inc
 * @author      Danila Vasenin <danila.vasenin@alpineinc.com>
 */

namespace Alpine\Acton\Plugin\Customer\Api\CustomerRepository;

use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Api\Data\CustomerSearchResultsInterface;

/**
 * Alpine\Acton\Plugin\Customer\Api\CustomerRepository\Query
 *
 * @category    Alpine
 * @package     Alpine_Acton
 */
class Query
{
    /**
     * After get customer.
     *
     * @param CustomerRepositoryInterface $subject
     * @param CustomerInterface $customer
     * @return CustomerInterface
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterGet(CustomerRepositoryInterface $subject, CustomerInterface $customer)
    {
        return $this->getCustomer($customer);
    }

    /**
     * After get customer list
     * 
     * @param CustomerRepositoryInterface $subject
     * @param CustomerSearchResultsInterface $entities
     *
     * @return CustomerSearchResultsInterface
     */
    public function afterGetList(
        CustomerRepositoryInterface $subject,
        $entities
    ) {
        foreach ($entities->getItems() as $entity) {
            $this->afterGet($subject, $entity);
        }

        return $entities;
    }

    /**
     * After get customer by ID.
     *
     * @param CustomerRepositoryInterface $subject
     * @param CustomerInterface $customer
     * @return CustomerInterface
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterGetById(CustomerRepositoryInterface $subject, CustomerInterface $customer)
    {
        return $this->getCustomer($customer);
    }

    /**
     * Get customer.
     *
     * @param CustomerInterface $customer
     * @return CustomerInterface
     */
    private function getCustomer(CustomerInterface $customer)
    {
        $extensionAttributes = $customer->getExtensionAttributes(); /** get current extension attributes from entity **/
        $newsletter = $customer->getCustomAttribute('newsletter');
        if ($newsletter) {
            $newsletter = $newsletter->getValue();
        }
        $extensionAttributes->setNewsletter($newsletter);
        $customer->setExtensionAttributes($extensionAttributes);
        return $customer;
    }
}
