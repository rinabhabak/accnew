<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Acart
 */


declare(strict_types=1);

namespace Amasty\Acart\Model\Schedule;

use Amasty\Acart\Api\Data\ScheduleInterface;
use Amasty\Acart\Api\Data\ScheduleInterfaceFactory;
use Amasty\Acart\Api\ScheduleRepositoryInterface;
use Amasty\Acart\Model\AbstractCachedRepository;
use Amasty\Acart\Model\ResourceModel\Schedule as ScheduleResource;
use Amasty\Acart\Model\Schedule as ScheduleModel;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NotFoundException;

class Repository extends AbstractCachedRepository implements ScheduleRepositoryInterface
{
    /**
     * @var ScheduleInterfaceFactory
     */
    private $scheduleFactory;

    /**
     * @var ScheduleResource
     */
    private $scheduleResource;

    public function __construct(
        ScheduleInterfaceFactory $scheduleFactory,
        ScheduleResource $scheduleResource
    ) {
        $this->scheduleFactory = $scheduleFactory;
        $this->scheduleResource = $scheduleResource;
    }

    private function getBy($value, $field = ScheduleModel::SCHEDULE_ID): ScheduleInterface
    {
        if (($result = $this->getFromCache($field, $value)) !== null) {
            return $result;
        }

        /** @var ScheduleInterface $schedule */
        $schedule = $this->scheduleFactory->create();
        $this->scheduleResource->load($schedule, $value, $field);
        if (!$schedule->getScheduleId()) {
            throw new NotFoundException(
                __('Schedule with specified %1 "%2"  not found.', $field, $value)
            );
        }

        return $this->addToCache($field, $value, $schedule);
    }

    public function getById(int $id): ScheduleInterface
    {
        return $this->getBy($id, ScheduleModel::SCHEDULE_ID);
    }

    public function save(ScheduleInterface $schedule): ScheduleInterface
    {
        try {
            $this->scheduleResource->save($schedule);
            $this->invalidateCache($schedule);
        } catch (\Exception $e) {
            throw new CouldNotSaveException(
                __('Unable to save the schedule. Error: %1', $e->getMessage())
            );
        }

        return $schedule;
    }

    public function delete(ScheduleInterface $schedule): bool
    {
        try {
            $this->scheduleResource->delete($schedule);
            $this->invalidateCache($schedule);
        } catch (\Exception $e) {
            throw new CouldNotDeleteException(
                __('Unable to delete the schedule. Error: %1', $e->getMessage())
            );
        }

        return true;
    }

    public function deleteById(int $id): bool
    {
        $this->delete($this->getById($id));

        return true;
    }
}
