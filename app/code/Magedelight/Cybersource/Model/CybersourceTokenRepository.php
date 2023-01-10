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

use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\Search\FilterGroup;
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magedelight\Cybersource\Api\Data;
use Magedelight\Cybersource\Api\Data\CybersourceTokenSearchResultsInterfaceFactory;
use Magedelight\Cybersource\Api\CybersourceTokenRepositoryInterface;
use Magedelight\Cybersource\Model\ResourceModel\Cards as SripeTokenResourceModel;
use Magedelight\Cybersource\Model\ResourceModel\Cards\Collection;
use Magedelight\Cybersource\Model\ResourceModel\Cards\CollectionFactory;

/**
 * Cybersource token repository
 */
class CybersourceTokenRepository implements CybersourceTokenRepositoryInterface
{
    /**
     * @var SripeTokenResourceModel
     */
    protected $resourceModel;

    /**
     * @var CardsFactory
     */
    protected $cardsFactory;

    /**
     * @var CybersourceTokenSearchResultsInterfaceFactory
     */
    protected $searchResultsFactory;

    /**
     * @var \Magento\Framework\Api\FilterBuilder
     */
    protected $filterBuilder;

    /**
     * @var \Magento\Framework\Api\SearchCriteriaBuilder
     */
    protected $searchCriteriaBuilder;

    /**
     * @var CollectionFactory
     */
    protected $collectionFactory;

    /**
     * @var \Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface
     */
    private $collectionProcessor;

   /**
    * 
    * @param SripeTokenResourceModel $resourceModel
    * @param \Magedelight\Cybersource\Model\CardsFactory $cardsFactory
    * @param FilterBuilder $filterBuilder
    * @param SearchCriteriaBuilder $searchCriteriaBuilder
    * @param CybersourceTokenSearchResultsInterfaceFactory $searchResultsFactory
    * @param CollectionFactory $collectionFactory
    * @param CollectionProcessorInterface $collectionProcessor
    */
    public function __construct(
        SripeTokenResourceModel $resourceModel,
        CardsFactory $cardsFactory,
        FilterBuilder $filterBuilder,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        CybersourceTokenSearchResultsInterfaceFactory $searchResultsFactory,
        CollectionFactory $collectionFactory,
        CollectionProcessorInterface $collectionProcessor = null
    ) {
        $this->resourceModel = $resourceModel;
        $this->cardsFactory = $cardsFactory;
        $this->filterBuilder = $filterBuilder;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->searchResultsFactory = $searchResultsFactory;
        $this->collectionFactory = $collectionFactory;
        $this->collectionProcessor = $collectionProcessor ?: $this->getCollectionProcessor();
    }

    /**
     * Lists cybersource tokens that match specified search criteria.
     *
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria The search criteria.
     * @return \Magedelight\Cybersource\Api\Data\CybersourceTokenSearchResultsInterface Cybersource token search result interface.
     */
    public function getList(\Magento\Framework\Api\SearchCriteriaInterface $searchCriteria)
    {
        
        /** @var \Magedelight\Cybersource\Model\ResourceModel\Cards\Collection $collection */
        $collection = $this->collectionFactory->create();
        $this->collectionProcessor->process($searchCriteria, $collection);
        /** @var \Magedelight\Cybersource\Api\Data\CybersourceTokenSearchResultsInterface $searchResults */
        $searchResults = $this->searchResultsFactory->create();
        $searchResults->setSearchCriteria($searchCriteria);
        $searchResults->setItems($collection->getItems());
        
        return $searchResults;
    }

    /**
     * Loads a specified cybersource token.
     *
     * @param int $entityId The payment token entity ID.
     * @return \Magedelight\Cybersource\Api\Data\CardManageInterface Cybersource token interface.
     */
    public function getById($entityId)
    {
        $cybersourceCardsModel = $this->cardsFactory->create();
        $this->resourceModel->load($cybersourceCardsModel, $entityId);
        return $cybersourceCardsModel;
    }

    /**
     * Deletes a specified payment token.
     *
     * @param int $entityId The payment token entity ID.
     * @return bool
     */
    public function delete($entityId)
    {
        $cybersourceCardsModel = $this->cardsFactory->create();
        $this->resourceModel->load($cybersourceCardsModel, $entityId);
        $cybersourceCardsModel->delete();
        return true;
    }

    /**
     * Performs persist operations for a specified payment token.
     *
     * @param \Magedelight\Cybersource\Api\Data\CardManageInterface $cybersourcecard The payment token.
     * @return \Magedelight\Cybersource\Api\Data\CardManageInterface Saved payment token data.
     */
    public function save(Data\CardManageInterface $cybersourcecard)
    {
        /** @var Cards $cybersourcecard */
        $this->resourceModel->save($cybersourcecard);
        return $cybersourcecard;
    }

    /**
     * Helper function that adds a FilterGroup to the collection.
     *
     * @param FilterGroup $filterGroup
     * @param Collection $collection
     * @return void
     * @deprecated 100.3.0
     * @throws \Magento\Framework\Exception\InputException
     */
    protected function addFilterGroupToCollection(FilterGroup $filterGroup, Collection $collection)
    {
        foreach ($filterGroup->getFilters() as $filter) {
            $condition = $filter->getConditionType() ? $filter->getConditionType() : 'eq';
            $collection->addFieldToFilter($filter->getField(), [$condition => $filter->getValue()]);
        }
    }

    /**
     * Retrieve collection processor
     *
     * @deprecated 100.3.0
     * @return CollectionProcessorInterface
     */
    private function getCollectionProcessor()
    {
        if (!$this->collectionProcessor) {
            $this->collectionProcessor = \Magento\Framework\App\ObjectManager::getInstance()->get(
                \Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface::class
            );
        }
        return $this->collectionProcessor;
    }
}
