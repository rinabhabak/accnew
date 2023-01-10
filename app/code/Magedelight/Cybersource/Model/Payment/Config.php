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
namespace Magedelight\Cybersource\Model\Payment;

class Config extends \Magento\Framework\Config\Data implements
\Magedelight\Cybersource\Model\Payment\ConfigInterface
{
    public function __construct(
        \Magedelight\Cybersource\Model\Payment\Reader $reader,
        \Magento\Framework\Config\CacheInterface $cache,
        $cacheId = 'payment_config'
    ) {
        parent::__construct($reader, $cache, $cacheId);
    }

    public function getCreditCardTypeInfo()
    {
        return $this->get();
    }
}
