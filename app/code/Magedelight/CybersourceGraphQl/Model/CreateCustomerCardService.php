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

use Magedelight\Cybersource\Api\Data\CardManageInterface;
use Magedelight\Cybersource\Api\Data\CardManageInterfaceFactory;
use Magedelight\Cybersource\Api\CybersourceTokenRepositoryInterface;
use Magedelight\Cybersource\Api\CardManagementInterfaceFactory;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;

/**
 * Class CreateCustomerCardService
 * @package Magedelight\CybersourceGraphQl\Model
 */
class CreateCustomerCardService
{
    /**
     * @var DataObjectHelper
     */
    private $dataObjectHelper;

    /**
     * @var CybersourceTokenRepositoryInterface
     */
    private $cybersourceTokenRepository;

    /**
     * @var CardManageInterfaceFactory
     */
    private $cardManageInterfaceFactory;

    /**
     * @var CardManagementInterfaceFactory
     */
    private $cardManagementInterfaceFactory;

    /**
     * CreateCustomerCardService constructor.
     * @param DataObjectHelper $dataObjectHelper
     * @param CybersourceTokenRepositoryInterface $cybersourceTokenRepository
     * @param CardManageInterfaceFactory $cardManageInterfaceFactory
     * @param CardManagementInterfaceFactory $cardManagementInterfaceFactory
     */
    public function __construct(
        DataObjectHelper $dataObjectHelper,
        CybersourceTokenRepositoryInterface $cybersourceTokenRepository,
        CardManageInterfaceFactory $cardManageInterfaceFactory,
        CardManagementInterfaceFactory $cardManagementInterfaceFactory
    ) {
        $this->dataObjectHelper = $dataObjectHelper;
        $this->cybersourceTokenRepository = $cybersourceTokenRepository;
        $this->cardManageInterfaceFactory = $cardManageInterfaceFactory;
        $this->cardManagementInterfaceFactory = $cardManagementInterfaceFactory;
    }

    /**
     * @param array $data
     * @param $customerId
     * @return array
     * @throws GraphQlInputException
     */
    public function execute(array $data,$customerId): array
    {
        try {
            $response = $this->createCard($data,$customerId);
        } catch (LocalizedException $e) {
            throw new GraphQlInputException(__($e->getMessage()));
        }

        return $response;
    }

    /**
     * @param array $data
     * @param $customerId
     * @return array
     * @throws LocalizedException
     */
    private function createCard(array $data,$customerId): array
    {
        /** @var CardManageInterface $cardDataObject */
        $cardDataObject = $this->cardManageInterfaceFactory->create();
        $this->dataObjectHelper->populateWithArray(
            $cardDataObject,
            $data,
            CardManageInterface::class
        );
        $cardManagement = $this->cardManagementInterfaceFactory->create();
        $response = $cardManagement->addCustomerCard($customerId,$cardDataObject);
        return $response;
    }
}