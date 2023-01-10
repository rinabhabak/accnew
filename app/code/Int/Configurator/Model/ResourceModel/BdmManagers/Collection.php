<?php
/**
 * Copyright © Indus Net Technologies All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Int\Configurator\Model\ResourceModel\BdmManagers;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
{
    /**
     * @var string
     */
    protected $_idFieldName = 'entity_id';

    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(
            \Int\Configurator\Model\BdmManagers::class,
            \Int\Configurator\Model\ResourceModel\BdmManagers::class
        );
    }

    public function addAssignToFilter($managertId)
    {
        $this->getSelect()->where("assigned_to =".$managertId);
        return $this;
    }

    public function addConfiguratorFilter($managertId)
    {
        $this->getSelect()->where("parent_id =".$managertId);
        return $this;
    }

    public function addCreatedByFilter($id)
    {
        $this->getSelect()->where('created_by = '.$id);
        return $this;
    }
}
