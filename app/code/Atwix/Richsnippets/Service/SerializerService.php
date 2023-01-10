<?php
/**
 * @author Atwix Team
 * @copyright Copyright (c) 2019 Atwix (https://www.atwix.com/)
 * @package Atwix_Richsnippets
 */

namespace Atwix\Richsnippets\Service;

if (class_exists('\Magento\Framework\Serialize\Serializer\Json')) {
    abstract class SerializerAbstract extends \Magento\Framework\Serialize\Serializer\Json
    {

    }
} else {
    abstract class SerializerAbstract
    {

    }
}

class SerializerService extends SerializerAbstract
{
    /**
     * Unserialize
     *
     * @param $string
     * @return array
     */
    public function unserialize($string)
    {
        if (method_exists(get_parent_class($this), 'unserialize')) {
            return parent::unserialize($string);
        } else {
            return unserialize($string);
        }
    }
}


