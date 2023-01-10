<?php
/**
 * Magedelight
 *
 * @category Magedelight
 * @package  Magedelight_CybersourceGraphQl
 * @author   Magedelight <info@magedelight.com>
 * @license  http://opensource.org/licenses/gpl-3.0.html GNU General Public License,version 3 (GPL-3.0)
 * @copyright Copyright (c) 2020 Magedelight (http://www.magedelight.com)
 */
declare(strict_types=1);

namespace Magedelight\CybersourcesopGraphQl\Model;

use Magento\QuoteGraphQl\Model\Cart\Payment\AdditionalDataProviderInterface;
use Magento\Framework\Stdlib\ArrayManager;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;

/**
 * SetPaymentMethod additional data provider model for Cybersourcesop payment method
 *
 *
 */
class CybersourcesopDataProvider implements AdditionalDataProviderInterface
{
    const PATH_ADDITIONAL_DATA = 'cybersourcesop';

    /**
     * @var ArrayManager
     */
    private $arrayManager;

    /**
     * @param ArrayManager $arrayManager
     */
    public function __construct(
        ArrayManager $arrayManager
    ) {
        $this->arrayManager = $arrayManager;
    }

    /**
     * Return additional data
     *
     * @param array $data
     * @return array
     * @throws GraphQlInputException
     */
    public function getData(array $data): array
    {
        $result = (isset($data[self::PATH_ADDITIONAL_DATA]))?$data[self::PATH_ADDITIONAL_DATA]:$data;
        return $result;
    }
}
