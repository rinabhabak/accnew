<?php

namespace Int\SalesRestApi\Model\ResourceModel\Order;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;

class Collection extends \Magento\Sales\Model\ResourceModel\Order\Collection
{
    public function addFieldToFilter($field, $condition = null)
    {
        if(is_array($field) && isset($field[0])){
            $field = $field[0];
        }
        if ($field === 'created_at') {
            if (is_array($condition)) {
                /** @var \Magento\Framework\Stdlib\DateTime\TimezoneInterface $timeZone */
                $timeZone = ObjectManager::getInstance()
                    ->get(TimezoneInterface::class);
                foreach ($condition as &$innerData) {
                    foreach ($innerData as $key => &$value) {
                        $dataTime = explode(" ", $value);
                        if(!isset($dataTime[1]) && $key == 'to'){
                            $value = date('Y-m-d 23:59:59', strtotime($value));
                        }

                        $innerData[$key] = $timeZone->convertConfigTimeToUtc($value);
                    }

                }
            }
        }

        return parent::addFieldToFilter($field, $condition);
    }
}