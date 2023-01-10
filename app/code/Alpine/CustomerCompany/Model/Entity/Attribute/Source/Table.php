<?php
/**
 * Alpine_CustomerCompany
 *
 * @category    Alpine
 * @package     Alpine_CustomerCompany
 * @copyright   Copyright (c) 2018 Alpine Consulting, Inc
 * @author      Andrey Nesterov <andrey.nesterov@alpineinc.com>
 */
namespace Alpine\CustomerCompany\Model\Entity\Attribute\Source;

use Magento\Eav\Model\Entity\Attribute\Source\Table as BaseTable;

/**
 * Alpine\CustomerCompany\Model\Entity\Attribute\Source\Table
 *
 * @category    Alpine
 * @package     Alpine_CustomerCompany
 */
class Table extends BaseTable
{
    /**
     * Retrieve Full Option values array
     *
     * @param bool $withEmpty
     * @param bool $defaultValues
     * @return array
     */
    public function getAllOptions($withEmpty = false, $defaultValues = false)
    {
        return parent::getAllOptions($withEmpty, $defaultValues);
    }
}
