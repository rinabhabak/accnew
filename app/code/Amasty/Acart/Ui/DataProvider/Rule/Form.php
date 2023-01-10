<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Acart
 */

declare(strict_types=1);

namespace Amasty\Acart\Ui\DataProvider\Rule;

use Amasty\Acart\Api\RuleRepositoryInterface;
use Amasty\Acart\Controller\Adminhtml\Rule\Save;
use Amasty\Acart\Model\ResourceModel\Rule\CollectionFactory;
use Amasty\Acart\Model\Rule;
use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Ui\DataProvider\AbstractDataProvider;

class Form extends AbstractDataProvider
{
    /**
     * @var array
     */
    private $loadedData;

    /**
     * @var DataPersistorInterface
     */
    private $dataPersistor;

    /**
     * @var RuleRepositoryInterface
     */
    private $ruleRepository;

    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        CollectionFactory $collectionFactory,
        DataPersistorInterface $dataPersistor,
        RuleRepositoryInterface $ruleRepository,
        array $meta = [],
        array $data = []
    ) {
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
        $this->collection = $collectionFactory->create();
        $this->dataPersistor = $dataPersistor;
        $this->ruleRepository = $ruleRepository;
    }

    public function getData()
    {
        if (isset($this->loadedData)) {
            return $this->loadedData;
        }
        $data = parent::getData();

        if (!$data['totalRecords']) {
            return [];
        }
        $ruleId = (int)$data['items'][0][Rule::RULE_ID];
        $rule = $this->ruleRepository->get($ruleId);
        $this->loadedData[$ruleId] = $rule->getData();

        $data = $this->dataPersistor->get(Save::DATA_PERSISTOR_KEY);

        if (!empty($data)) {
            $rule = $this->collection->getNewEmptyItem();
            $rule->setData($data);
            $this->loadedData[$rule->getRuleId()] = $rule->getData();
            $this->dataPersistor->clear(Save::DATA_PERSISTOR_KEY);
        }

        return $this->loadedData;
    }
}
