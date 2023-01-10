<?php
namespace Int\WebformsGraphQl\Model\Resolver;

use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Exception\GraphQlNoSuchEntityException;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Framework\Exception\AuthenticationException;
use Magento\Framework\GraphQl\Exception\GraphQlAuthorizationException;
use VladimirPopov\WebForms\Model\ResourceModel\Field\CollectionFactory as FieldCollectionFactory;
use Magento\Framework\App\Action\Context;
use Magento\Framework\DataObject;
use VladimirPopov\WebForms\Model\FormFactory;
use Magento\Cms\Model\Template\FilterProvider;


class SubmitWebForms implements ResolverInterface
{
	protected $_savePostResult;
	protected $messageManager;
	protected $_formFactory;
	protected $_filterProvider;
	protected $_fileCollection;
	protected $_storeManager;

    public function __construct(
    	\Magento\Store\Model\StoreManagerInterface $StoreManagerInterface,
        \VladimirPopov\WebForms\Model\ResourceModel\Form\CollectionFactory $formCollectionFactory,
        \VladimirPopov\WebForms\Model\ResourceModel\File\CollectionFactory $fileCollectionFactory,
        FieldCollectionFactory $fieldCollectionFactory,
        \Int\WebformsGraphQl\Model\Form $SavePostResult,
        Context $context,
        FormFactory $formFactory,
        FilterProvider $filterProvider

    ) {
        $this->formCollectionFactory 	= $formCollectionFactory;
        $this->fieldCollectionFactory 	= $fieldCollectionFactory;
        $this->_savePostResult 			= $SavePostResult;
        $this->messageManager          	= $context->getMessageManager();
        $this->_formFactory            	= $formFactory;
        $this->_filterProvider         	= $filterProvider;
        $this->_fileCollection         	= $fileCollectionFactory;
        $this->_storeManager         	= $StoreManagerInterface;
    }

    /**
     * @inheritdoc
     */
    public function resolve(
        Field $field,
        $context,
        ResolveInfo $info,
        array $value = null,
        array $args = null
    ){
        try {

	            if (!isset($args['input']['formId'])) {
	                throw new GraphQlInputException(__('"formId" value should be specified'));
	            }

	            $getFormData =[];
	            $field =[];
	            $message = [];
	            $customerId = $context->getUserId();
	            $successText = '';
	            $storeId = (int) $context->getExtensionAttributes()->getStore()->getId();
	            $webformCollection = $this->formCollectionFactory->create()->addFieldToFilter('id', ['eq' => $args['input']['formId']]);
	            $webform = $this->_formFactory->create()
	            ->setStoreId($storeId)
	            ->load($args['input']['formId']);
	            $filter  = $this->_filterProvider->getPageFilter();

	            if (!$webform->getIsActive()) {
	                throw new GraphQlInputException(__('"formId" value should be specified'));
	            }

	            if ($webform->getData() > 0) 
	            {
	                $successText = $filter->filter($webform->getSuccessText());

	                $getFormData['submitForm_'.$webform->getId()] = 1;
	                $getFormData['webform_id'] = $webform->getId();
	                $getFormData['formname'] = $webform->getName();

	                $fields = $this->fieldCollectionFactory->create()->addFilter('webform_id', $webform->getId())->addFilter('is_active', 1);

					foreach ($fields as $field) {

						if ($field->getType() == 'hidden') {
						    $getFormData[$field->getCode()] = $field->getvalue()['hidden'];
						}

					}
	            }
	                        
	            foreach ($args['input']['formData'] as $value) {
	                $getFormData['field'][$value['fieldId']] = $value['fieldValue'];
	            }

	            $savePostData = $this->_savePostResult->savePostResultPwa($getFormData,$args['input']['formId'],$customerId);

	            if ($savePostData->getId()) 
	            {
					
					$webformObject = new DataObject;
					$webformObject->setData($webform->getData());
					$subject = $savePostData->getEmailSubject('customer');

					$filter->setVariables(array(
						'webform_result' => $savePostData->toHtml('customer'),
						'result' => $savePostData->getTemplateResultVar(),
						'webform' => $savePostData,
						'webform_subject' => $subject
					));

					if ($webform->getRedirectUrl()) {
					    $message['redirect_url'] =  $webform->getRedirectUrl();
					}

					$files =  $this->getFilesFromResultId($savePostData->getId());

					if (!empty($files)){
					    $message['files_url'] =  $files;
					}

					$message['message'] = $successText;

	            } else {
	                $errors = $this->messageManager->getMessages(true)->getItems();
	                foreach ($errors as $err) {
	                    $result["errors"][] = $err->getText();
	                }
	                $html_errors = "";
	                if (count($result["errors"]) > 1) {
	                    foreach ($result["errors"] as $err) {
	                        $html_errors .= '<p>' . $err . '</p>';
	                    }
	                    $result["errors"] = $html_errors;
	                } else {
	                    $result["errors"] = $result["errors"][0];
	                }
	            }

        } catch (\Exception $e) {
            throw new GraphQlInputException( __($e->getMessage()) );
        }

        return $message;

    }

    /**
     * @param string $result_id
     * @return string | null
     */
    protected function getFilesFromResultId($result_id)
    {
    	if(empty($result_id)){
    		return null;
    	}

    	$return_urls = [];
    	$collection = $this->_fileCollection->create();
    	$collection->addFieldToFilter('result_id', $result_id);
    	$webform_url = $this->_storeManager->getStore(0)->getBaseUrl(). 'webforms/file/download/hash/';
    	
    	if($collection->count() > 0)
    	{
    		foreach ($collection as $value) {
    			$return_urls[] = $webform_url.$value['link_hash'];
    		}
    	}

    	if(!empty($return_urls)){
    		return implode(',', $return_urls);
    	}

    	return null;

    }
}