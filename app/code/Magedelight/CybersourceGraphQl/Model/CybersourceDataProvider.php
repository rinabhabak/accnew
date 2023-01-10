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

namespace Magedelight\CybersourceGraphQl\Model;

use Magento\QuoteGraphQl\Model\Cart\Payment\AdditionalDataProviderInterface;
use Magento\Framework\Stdlib\ArrayManager;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;

/**
 * SetPaymentMethod additional data provider model for Cybersource payment method
 *
 *
 */
class CybersourceDataProvider implements AdditionalDataProviderInterface
{
    private const PATH_ADDITIONAL_DATA = 'magedelight_cybersource';

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
        if (!isset($data[self::PATH_ADDITIONAL_DATA])) {
            throw new GraphQlInputException(
                __('Required parameter "cybersource token" for "payment_method" is missing.')
            );
        }
        if (!isset($data[self::PATH_ADDITIONAL_DATA]['subscription_id'])) {
            throw new GraphQlInputException(
                __('Required parameter "subscription_id" for "cybersource tokenization" is missing.')
            );
        }
        if (!isset($data[self::PATH_ADDITIONAL_DATA]['cc_number'])) {
            throw new GraphQlInputException(
                __('Required parameter "cc_number" for "cybersource tokenization" is missing.')
            );
        }
        if (!isset($data[self::PATH_ADDITIONAL_DATA]['cc_type'])) {
            throw new GraphQlInputException(
                __('Required parameter "cc_type" for "cybersource tokenization" is missing.')
            );
        }
        if (!isset($data[self::PATH_ADDITIONAL_DATA]['expiration'])) {
            throw new GraphQlInputException(
                __('Required parameter "expiration" for "cybersource tokenization" is missing.')
            );
        }
        if (!isset($data[self::PATH_ADDITIONAL_DATA]['expiration_yr'])) {
            throw new GraphQlInputException(
                __('Required parameter "expiration_yr" for "cybersource tokenization" is missing.')
            );
        }
        if (!isset($data[self::PATH_ADDITIONAL_DATA]['save_card'])) {
            throw new GraphQlInputException(
                __('Required parameter "save_card" for "cybersource tokenization" is missing.')
            );
        }
        if (!isset($data[self::PATH_ADDITIONAL_DATA]['cc_cid'])) {
            throw new GraphQlInputException(
                __('Required parameter "cc_cid" for "cybersource tokenization" is missing.')
            );
        }
        $additionalData = $this->arrayManager->get(static::PATH_ADDITIONAL_DATA, $data);

        return $additionalData;
    }
}
