<?php
/**
 * Magedelight
 * Copyright (C) 2019 Magedelight <info@magedelight.com>
 *
 * @category Magedelight
 * @package Magedelight_Cybersource
 * @copyright Copyright (c) 2019 Mage Delight (http://www.magedelight.com/)
 * @license http://opensource.org/licenses/gpl-3.0.html GNU General Public License,version 3 (GPL-3.0)
 * @author Magedelight <info@magedelight.com>
 */
namespace Magedelight\Cybersource\Model\Source;

class CcType extends \Magento\Payment\Model\Source\Cctype
{
    public function getAllowedTypes()
    {
        return ['VI', 'MC', 'AE', 'DI', 'JCB', 'OT', 'MAESTRO', 'SWITCH', 'DC'];
    }
    protected $objectManager;
    protected $cardConfig;
    public function __construct(
        \Magento\Framework\ObjectManager\ObjectManager $objectManager,
        \Magedelight\Cybersource\Model\CardConfig $cardConfig
    ) {
        $this->objectManager = $objectManager;
        $this->cardConfig = $cardConfig;
    }
    public function toOptionArray()
    {
        //        echo "hello";
//        die();
         $options = [];

        foreach ($this->cardConfig->getCcTypesCybersource() as $code => $name) {
            if (in_array($code, $this->getAllowedTypes())) {
                $options[] = ['value' => $code, 'label' => $name];
            }
        }
       // $options[] = ['value' => "test", 'label' => "test"];
        // return $this->objectManager->get('Magedelight\Cybersource\Model\Cardconfig')->getCcTypesCybersource();
       return $options;
//         return $this->cardConfig->getCcTypesCybersource();
    }

//    public function toOption()
//    {
//        $options =  array();
//
//        foreach ($this->objectFactory->get('Magedelight\Cybersource\Model\Cardconfig')->getCcTypesCybersource() as $code => $name) {
//            $options[$code] = $name;
//        }
//
//        return $options;
//    }
}
