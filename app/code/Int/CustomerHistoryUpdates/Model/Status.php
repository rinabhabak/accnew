<?php
namespace Int\CustomerHistoryUpdates\Model;
class Status extends \Magento\Framework\Model\AbstractModel implements \Magento\Framework\DataObject\IdentityInterface
{
	const CACHE_TAG = 'customer_status_history';

	protected $_cacheTag = 'customer_status_history';

	protected $_eventPrefix = 'customer_status_history';

	protected function _construct()
	{
		$this->_init('Int\CustomerHistoryUpdates\Model\ResourceModel\Status');
	}

	public function getIdentities()
	{
		return [self::CACHE_TAG . '_' . $this->getId()];
	}

	public function getDefaultValues()
	{
		$values = [];

		return $values;
	}
}