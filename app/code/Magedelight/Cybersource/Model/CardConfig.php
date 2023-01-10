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
namespace Magedelight\Cybersource\Model;

class CardConfig
{
    protected $objectFactory;
    public function __construct(
       \Magento\Framework\ObjectManagerInterface $objectFactory
        ) {
        $this->objectFactory = $objectFactory;
    }

    public function getCcTypesCybersource()
    {
        $testConfig = $this->objectFactory->get('Magedelight\Cybersource\Model\Payment\ConfigInterface');
        $types = $testConfig->getCreditCardTypeInfo()['credit_cards'];
        //uasort($types, array($this, "cctypesSort"));
        return $types;
    }

    public function cctypesSort($a, $b)
    {
        return strcmp($a, $b);
    }
}
