<?php
/**
 * Magedelight
 *
 * @category Magedelight
 * @package  Magedelight_CybersourcesopGraphQl
 * @author   Magedelight <info@magedelight.com>
 * @license  http://opensource.org/licenses/gpl-3.0.html GNU General Public License,version 3 (GPL-3.0)
 * @copyright Copyright (c) 2020 Magedelight (http://www.magedelight.com)
 */
declare(strict_types=1);

namespace Magedelight\CybersourcesopGraphQl\Model\Resolver;

use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magedelight\Cybersourcesop\Gateway\Config\Config;
use Magento\Payment\Model\Config as PaymentConfig;

/**
 * Class CybersourcesopInit
 * @package Magedelight\CybersourceGraphQl\Model\Resolver
 */
class CybersourcesopInit implements ResolverInterface
{
   /**
     * @var Config
     */
    private $config;
    
    
    public function __construct(
        Config $config,
        PaymentConfig $paymentConfig    
        )
    {
        $this->config = $config;
        $this->paymentConfig = $paymentConfig;
    }

    /**
     * @inheritdoc
     */
    public function resolve(Field $field, $context, ResolveInfo $info, array $value = null, array $args = null)
    {
        try {
            $this->config->setMethodCode('cybersourcesop');
            $result['active'] = $this->config->getIsActive();
            $result['title'] = $this->config->getMethodTitle();
            $result['sandbox_flag'] = $this->config->getIsTestMode();
            $result['cctypes'] = $this->getCcAvailableTypes();
            $result['iframe_action_url'] = ($this->config->getIsTestMode())?$this->config->getCgiUrlTestMode() :$this->config->getCgiUrl();
            $this->config->setMethodCode('cybersourcesop_cc_vault');
            $result['cybersourcesop_cc_vault_active'] = $this->config->getIsVaultActive();
            return $result;
        } catch (\Exception $e) {
            throw new GraphQlInputException(__('Payment Token Build Error.'));
        }
    }
    
    private function getCcAvailableTypes()
    {
        $types = $this->paymentConfig->getCcTypes();
        $availableTypes = $this->config->getCcTypes();
        if ($availableTypes) {
            foreach ($types as $code => $name) {
                if (!in_array($code, $availableTypes)) {
                    unset($types[$code]);
                }
            }
        }
        foreach ($types as $code => $name) {
                $options[] = ['code' => $code, 'label' => $name];
        }
        return $options;
    }
}