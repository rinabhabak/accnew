<?php
namespace SLI\Feed\Model\Generators\Helpers;

use Magento\Customer\Api\GroupRepositoryInterface;
use Magento\Framework\Api\SearchCriteria;
use Magento\Framework\Api\SearchCriteriaBuilder;

/**
 * Customer Map Loader
 *
 * Obtains a map of id -> group names.
 */
class GroupMapLoader
{
    /**
     * Hard coded size of pages for the Group Repository's iterating.
     */
    const CUSTOMER_GROUP_PAGE_SIZE = 1000;

    /** Service Contract for Customer Groups.
     *
     * @var GroupRepositoryInterface
     */
    protected $customerGroupRepository;

    /** Builder to make search criteria for getting data against a Repository.
     *
     * @var SearchCriteriaBuilder
     */
    protected $searchCriteriaBuilder;

    /**
     *
     * CustomerMapLoader constructor.
     * @param GroupRepositoryInterface $groupRepository
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     */
    public function __construct(
        GroupRepositoryInterface $groupRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder
    )
    {
        $this->customerGroupRepository = $groupRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
    }


    /**
     * Returns an group id->name associative array.
     *
     * @return array
     */
    public function load()
    {
        $groupMap = [];

        /** @var SearchCriteria $searchCriteria */
        $searchCriteria = $this->searchCriteriaBuilder
            ->setPageSize(self::CUSTOMER_GROUP_PAGE_SIZE)
            ->create();

        foreach($this->customerGroupRepository->getList($searchCriteria)->getItems() as $group) {
            $groupMap[$group->getId()] = $group->getCode();
        }

        return $groupMap;
    }

}