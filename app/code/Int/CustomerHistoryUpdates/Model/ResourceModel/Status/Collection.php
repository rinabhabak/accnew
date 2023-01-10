<?php
namespace Int\CustomerHistoryUpdates\Model\ResourceModel\Status;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
	protected $_idFieldName = 'id';
	protected $_eventPrefix = 'int_customerhistoryupdates_status_collection';
	protected $_eventObject = 'status_collection';

	/**
	 * Define resource model
	 *
	 * @return void
	 */
	protected function _construct()
	{
		$this->_init('Int\CustomerHistoryUpdates\Model\Status', 'Int\CustomerHistoryUpdates\Model\ResourceModel\Status');
	}

}
