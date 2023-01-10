<?php

namespace Int\BdmManagement\Model;

/**
 * Status Model
 */
class Status extends \Magento\Framework\Model\AbstractModel
{
    
    public function toOptionArray(){
        return [
            ['value' => 1, 'label' => 'Active'],
            ['value' => 0, 'label' => 'Deactive']
        ];
    }
    
    
    public function getOptionArray(){
        return [
            1 => 'Active',
            0 => 'Deactive'
        ];
    }
    
    
}