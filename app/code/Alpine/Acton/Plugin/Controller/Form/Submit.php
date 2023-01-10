<?php
/**
 * Alpine_Acton
 *
 * @category    Alpine
 * @package     Alpine_Acton
 * @copyright   Copyright (c) 2018 Alpine Consulting, Inc
 * @author      Andrey Nesterov <andrey.nesterov@alpineinc.com>
 */

namespace Alpine\Acton\Plugin\Controller\Form;

use Alpine\Acton\Helper\Data;
use VladimirPopov\WebForms\Controller\Form\Submit as BaseSubmit;
use VladimirPopov\WebForms\Model\FieldFactory;
use VladimirPopov\WebForms\Model\ResourceModel\File\CollectionFactory;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\App\Response\Http;
use Magento\Framework\App\RequestInterface;
use Psr\Log\LoggerInterface;

/**
 * Alpine\Acton\Plugin\Controller\Form\Submit
 *
 * @category    Alpine
 * @package     Alpine_Acton
 */
class Submit
{
    /**
     * Helper
     *
     * @var Data
     */
    protected $helper;

    /**
     * Json serializer
     *
     * @var Json
     */
    protected $jsonSerializer;
    
    /**
     * Request
     *
     * @var RequestInterface
     */
    protected $request;
    
    /**
     * Field factory
     *
     * @var FieldFactory
     */
    protected $fieldFactory;
    
    /**
     * File collection factory
     *
     * @var CollectionFactory
     */
    protected $fileCollectionFactory;
    
    /**
     * Logger
     *
     * @var LoggerInterface
     */
    protected $logger;
    
    /**
     * Constructor
     *
     * @param Data $helper
     * @param Json $jsonSerializer
     * @param RequestInterface $request
     * @param FieldFactory $fieldFactory
     * @param CollectionFactory $fileCollectionFactory
     * @param LoggerInterface $logger
     */
    public function __construct(
        Data $helper,
        Json $jsonSerializer,
        RequestInterface $request,
        FieldFactory $fieldFactory,
        CollectionFactory $fileCollectionFactory,
        LoggerInterface $logger
    ) {
        $this->helper = $helper;
        $this->jsonSerializer = $jsonSerializer;
        $this->request = $request;
        $this->fieldFactory = $fieldFactory;
        $this->fileCollectionFactory = $fileCollectionFactory;
        $this->logger = $logger;
    }

    /**
     * After execute
     *
     * @param BaseSubmit $subject
     * @param Http $result
     * @return Http
     */
    public function afterExecute(
        BaseSubmit $subject,
        Http $result
    ) {
        try {
            $content = $result->getContent();
            $contentData = $this->jsonSerializer->unserialize($content);
            
            $webformId = $this->request->getParam('webform_id');
            $fields = $this->request->getParam('field');
            
            $contentData['url'] = $this->getWebformSubmitUrlById($webformId);
            if($fields){
                $contentData['fields'] = $this->prepareFormFields($fields);
            }
            
            $content = $this->jsonSerializer->serialize($contentData);
            $result->setContent($content);
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
        }
        
        return $result;
    }
    
    /**
     * Get webform submit url by id
     *
     * @param string $id
     * @return string
     */
    protected function getWebformSubmitUrlById($id)
    {
        $mapping = $this->helper->getFormsMapping();
        $mappingArray = $this->jsonSerializer->unserialize($mapping);
        
        return $mappingArray[$id] ?? '';
    }
    
    /**
     * Prepare form fields
     *
     * @param array $fields
     * @return array
     */
    protected function prepareFormFields(array $fields)
    {
        $result = [];
        
        $resultId = $this->request->getParam('result_id');
        foreach ($fields as $fieldId => $value) {
            $field = $this->fieldFactory->create()
                ->load($fieldId);
            if ($field->getId()) {
                $fieldCode = $field->getCode();
                
                if ($field->getType() == 'file' && $resultId) {
                    $files = $this->fileCollectionFactory->create()
                        ->addFilter('result_id', $resultId)
                        ->addFilter('field_id', $fieldId);
                    
                    $links = [];
                    foreach ($files as $file) {
                        $links[] = $file->getDownloadLink(false);
                    }
                    $result[$fieldCode] = implode(', ', $links);
                } else {
                    $result[$fieldCode] = is_array($value)
                        ? $value[0]
                        : $value;
                }
            }
        }
        
        return $result;
    }
}
