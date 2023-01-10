<?php
/**
 * Alpine_Storelocator
 *
 * @category    Alpine
 * @package     Alpine_Storelocator
 * @copyright   Copyright (c) 2018 Alpine Consulting, Inc
 * @author      Andrey Nesterov <andrey.nesterov@alpineinc.com>
 */
namespace Alpine\Storelocator\Model\Import;

use Amasty\Base\Model\Serializer;
use Amasty\Storelocator\Helper\Data;
use Amasty\Storelocator\Model\Import\Location as BaseLocation;
use Amasty\Storelocator\Model\Import\Proxy\Location\ResourceModelFactory;
use Amasty\Storelocator\Model\Import\Validator;
use Amasty\Storelocator\Model\Import\Validator\Country;
use Amasty\Storelocator\Model\Import\Validator\Photo;
use Amasty\Storelocator\Model\LocationFactory;
use Amasty\Storelocator\Model\ResourceModel\Attribute\CollectionFactory;
use Magento\CatalogImportExport\Model\Import\UploaderFactory;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\File\ReadFactory;
use Magento\Framework\HTTP\Adapter\CurlFactory;
use Magento\Framework\Json\Helper\Data as JsonHelper;
use Magento\Framework\Stdlib\StringUtils;
use Magento\ImportExport\Helper\Data as ImportExportData;
use Magento\ImportExport\Model\Import;
use Magento\ImportExport\Model\Import\ErrorProcessing\ProcessingErrorAggregatorInterface;
use Magento\ImportExport\Model\ResourceModel\Helper;
use Magento\ImportExport\Model\ResourceModel\Import\Data as ImportData;

/**
 * Alpine\Storelocator\Model\Import\Location
 *
 * @category    Alpine
 * @package     Alpine_Storelocator
 */
class Location extends BaseLocation
{
    /**
     * Location Factory
     *
     * @var LocationFactory
     */
    protected $locationFactory;
    
    /**
     * Attribute collection factory
     *
     * @var CollectionFactory
     */
    protected $attributeCollectionFactory;
    
    /**
     * Serializer
     *
     * @var Serializer
     */
    protected $serializer;

    /**
     * Constructor
     *
     * @param JsonHelper $jsonHelper
     * @param ImportExportData $importExportData
     * @param ImportData $importData
     * @param ResourceConnection $resource
     * @param Helper $resourceHelper
     * @param StringUtils $string
     * @param ProcessingErrorAggregatorInterface $errorAggregator
     * @param UploaderFactory $uploaderFactory
     * @param Filesystem $filesystem
     * @param ReadFactory $readFactory
     * @param CurlFactory $curlFactory
     * @param ScopeConfigInterface $scopeConfig
     * @param ResourceModelFactory $resourceModelFactory
     * @param Country $validatorCountry
     * @param Photo $validatorPhoto
     * @param CollectionFactory $attributeCollectionFactory
     * @param LocationFactory $locationFactory
     * @param Data $dataHelper
     * @param Validator $validator
     * @param Serializer $serializer
     */
    public function __construct(
        JsonHelper $jsonHelper,
        ImportExportData $importExportData,
        ImportData $importData,
        ResourceConnection $resource,
        Helper $resourceHelper,
        StringUtils $string,
        ProcessingErrorAggregatorInterface $errorAggregator,
        UploaderFactory $uploaderFactory,
        Filesystem $filesystem,
        ReadFactory $readFactory,
        CurlFactory $curlFactory,
        ScopeConfigInterface $scopeConfig,
        ResourceModelFactory $resourceModelFactory,
        Country $validatorCountry,
        Photo $validatorPhoto,
        CollectionFactory $attributeCollectionFactory,
        LocationFactory $locationFactory,
        Data $dataHelper,
        Validator $validator,
        Serializer $serializer
    ) {
        parent::__construct(
            $jsonHelper,
            $importExportData,
            $importData,
            $resource,
            $resourceHelper,
            // $string,
            $errorAggregator,
            $uploaderFactory,
            $filesystem,
            $readFactory,
            $curlFactory,
            $scopeConfig,
            $resourceModelFactory,
            $validatorCountry,
            $validatorPhoto,
            $attributeCollectionFactory,
            $locationFactory,
            $dataHelper,
            $validator
        );
        
        $this->locationFactory = $locationFactory;
        $this->serializer = $serializer;
        $this->attributeCollectionFactory = $attributeCollectionFactory;
    }

    /**
     * Save product prices.
     *
     * @param array  $locations
     * @param string $table
     * @return $this
     */
    protected function saveLocation(array $locations, $table)
    {
        $tableName = $this->_resourceFactory->create()->getTable($table);
        $delLocationIds = [];
        foreach ($locations as $location) {
            if (isset($location[self::COL_ID])) {
                $delLocationIds[] = $location[self::COL_ID];
            }
        }
        if (Import::BEHAVIOR_APPEND != $this->getBehavior()) {
            $this->_connection->delete(
                $tableName,
                $this->_connection->quoteInto('id IN (?)', $delLocationIds)
            );
        }
        
        $attributes = $this->getAttributesWithOptions();

        foreach ($locations as $location) {
            $locationData = $this->prepareData($location, $attributes);
            $locationModel = $this->locationFactory->create();
            $locationModel->addData($locationData);
            $locationModel->save();
        }

        return $this;
    }

    /**
     * Prepare data for saving
     *
     * @param array $locationData
     * @param array $attributes
     * @return array $locationData
     */
    protected function prepareData($locationData, $attributes)
    {
        $attributeCodes = $this->attributeCollection->getColumnValues('attribute_code');
        
        foreach ($locationData as $key => $value) {
            if (in_array($key, $attributeCodes)) {
                if (array_key_exists($key, $attributes)) {
                    $attribute = $attributes[$key];
                    $attributeId = $attribute['attribute_id'];
                    if (isset($attribute['options'][$value])) {
                        $locationData['store_attribute'][$attributeId] = $attribute['options'][$value];
                    } else {
                        $locationData['store_attribute'][$attributeId] = $value;
                    }
                } else {
                    $locationData['store_attribute'][$attributeId] = $value;
                }
            } elseif (in_array($key, $this->scheduleColumnNames)) {
                $defaultTime = [
                    '0' => '00',    // default hours
                    '1' => '00'   // default minutes
                ];
                $locationData['schedule'][$key]['from'] = $locationData['schedule'][$key]['to'] = $defaultTime;

                $dayTimes = explode('-', $value);
                if (isset($dayTimes[0], $dayTimes[1])) {
                    $allDays = array_merge(explode(':', $dayTimes[0]), explode(':', $dayTimes[1]));
                    if (count($allDays) == 4) {
                        $locationData['schedule'][$key]['from'][0] = $allDays[0];
                        $locationData['schedule'][$key]['from'][1] = $allDays[1];
                        $locationData['schedule'][$key]['to'][0] = $allDays[2];
                        $locationData['schedule'][$key]['to'][1] = $allDays[3];
                    }
                }
            }
        }

        return $locationData;
    }
    
    /**
     * Get attributes with options
     *
     * @return array
     */
    protected function getAttributesWithOptions()
    {
        $result = [];
        $attributes = $this->attributeCollectionFactory->create()
            ->joinAttributes()
            ->getAttributes();
        
        foreach ($attributes as $attribute) {
            $attrCode = $attribute['attribute_code'];
            if (!array_key_exists($attrCode, $result)) {
                $result[$attrCode] = [
                    'attribute_id' => $attribute['attribute_id'],
                    'options' => []
                ];
            }
            
            if ($attribute['frontend_input'] == 'boolean') {
                $result[$attrCode]['options'][0] = __('No');
                $result[$attrCode]['options'][1] = __('Yes');
            } else {
                $options = $this->serializer->unserialize($attribute['options_serialized']);
                $optionLabel = $options[0];
                $result[$attrCode]['options'][$optionLabel] = $attribute['value_id'];
            }
        }
        
        return $result;
    }
}
