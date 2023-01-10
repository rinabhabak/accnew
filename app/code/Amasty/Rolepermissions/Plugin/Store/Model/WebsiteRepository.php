<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Rolepermissions
 */

declare(strict_types=1);

namespace Amasty\Rolepermissions\Plugin\Store\Model;

use Amasty\Rolepermissions\Helper\Data;
use Amasty\Rolepermissions\Model\Rule;
use Magento\Framework\Registry;

class WebsiteRepository
{
    const AM_USE_ALL_WEBSITES = 'am_use_all_websites';

    /**
     * @var Data
     */
    private $helper;

    /**
     * @var Registry
     */
    private $coreRegistry;

    public function __construct(
        Data $helper,
        Registry $coreRegistry
    ) {
        $this->helper = $helper;
        $this->coreRegistry = $coreRegistry;
    }

    /**
     * @param \Magento\Store\Model\WebsiteRepository $subject
     * @param \Magento\Store\Api\Data\WebsiteInterface[]  $result
     *
     * @return array
     */
    public function afterGetList(
        \Magento\Store\Model\WebsiteRepository $subject,
        $result
    ) {
        $rule = $this->helper->currentRule();

        if ($rule && $this->isNeedRestrict($rule)) {
            foreach ($result as $key => $website) {
                $websiteId = $website->getId();
                $accessible = in_array($websiteId, $rule->getPartiallyAccessibleWebsites());

                if (!$accessible && $websiteId != 0) {
                    unset($result[$key]);
                }
            }
        }

        return $result;
    }

    private function isNeedRestrict(Rule $rule): bool
    {
        return !$this->coreRegistry->registry(self::AM_USE_ALL_WEBSITES)
            && ($rule->getScopeWebsites() || $rule->getScopeStoreviews());
    }
}
