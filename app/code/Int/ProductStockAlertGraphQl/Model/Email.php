<?php

namespace Int\ProductStockAlertGraphQl\Model;

class Email extends \Bss\ProductStockAlert\Model\Email
{
    public function setCustomerName($customerName)
    {
        if($customerName == "Guest"){
            $this->_customerName = "Hello!";
        }else {
            $this->_customerName = "Hello ".$customerName.",";
        }
        return $this;
    }
}