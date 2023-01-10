<?php
/**
 * Indusnet Technologies.
 *
 * @category  Indusnet
 * @package   Int_WebformsGraphQl
 * @author    Indusnet
 */
namespace Int\WebformsGraphQl\Model\Resolver;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Exception\GraphQlNoSuchEntityException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\CustomerGraphQl\Model\Customer\GetCustomer;
use Magento\Framework\GraphQl\Exception\GraphQlAuthorizationException;
use Magento\Framework\Data\Form\FormKey;
use VladimirPopov\WebForms\Model\ResourceModel\Field\CollectionFactory as FieldCollectionFactory;
use VladimirPopov\WebForms\Model\ResourceModel\Fieldset\CollectionFactory as FieldsetCollectionFactory;
use Alpine\Acton\Helper\Data;
use Magento\Framework\Serialize\Serializer\Json;
/**
 * Class WebForms
 * @package Int\WebformsGraphQl\Model\Resolver
 */
class WebForms implements ResolverInterface 
{
    /**
     * @var \Magento\Directory\Model\RegionFactory
     */
    protected $_webform;

    /**
     * @var FormKey
     */
    protected $formKey;

    /**
     * @var FieldCollectionFactory
     */
    protected $fieldCollectionFactory;

    /**
     * @var FieldsetCollectionFactory
     */
    protected $fieldsetCollectionFactory;

    /**
     * @var Data
     */
    protected $helper;

     /**
     * @var Json
     */
    protected $jsonSerializer;

    public function __construct(
        \VladimirPopov\WebForms\Block\Form $webform, 
        \VladimirPopov\WebForms\Model\FormFactory $formFactory, 
        \VladimirPopov\WebForms\Model\ResourceModel\Form\CollectionFactory $formCollectionFactory, 
        FormKey $formKey, 
        FieldCollectionFactory $fieldCollectionFactory, 
        FieldsetCollectionFactory $fieldsetCollectionFactory,
        Data $helper,
        Json $jsonSerializer
    ){
        $this->_webform = $webform;
        $this->helper = $helper;
        $this->formFactory = $formFactory;
        $this->formCollectionFactory = $formCollectionFactory;
        $this->formKey = $formKey;
        $this->fieldCollectionFactory = $fieldCollectionFactory;
        $this->fieldsetCollectionFactory = $fieldsetCollectionFactory;
        $this->jsonSerializer = $jsonSerializer;
    }
    /**
     * @inheritdoc
     */
    public function resolve(Field $field, $context, ResolveInfo $info, array $value = null, array $args = null) {
        try {
            if (!isset($args['formId'])) {
                throw new GraphQlInputException(__('Form id should be specified'));
            }
            $formId = $args['formId'];
            $GetFormData = [];
            $positionArray = [];
            $fieldPosition = '';
            $validation_advice = '';
            $webformCollection = $this->formCollectionFactory->create()->addFieldToFilter('id', ['eq' => $formId]);
            $fieldsetCollection = $this->fieldsetCollectionFactory->create()->addFieldToFilter('webform_id', ['eq' => $formId]);
            if ($webformCollection->getSize() > 0) {
                if ($fieldsetCollection->getData() > 0) {
                    foreach ($fieldsetCollection->getData() as $position) {
                        $positionArray[$position['id']] = $position['name'];
                    }
                }
                $GetFormData['form_key'] = $this->getFormKey();
                $GetFormData['webform_id'] = $webformCollection->getData()[0]['id'];
                $GetFormData['description'] = $webformCollection->getData() [0]['description'];
                $GetFormData['act_on_url'] = $this->getWebformSubmitUrlById($formId);
                $i = 0;
                $j = 0;
                foreach ($webformCollection as $formdata) {
                    $fields = $this->fieldCollectionFactory->create()->addFilter('webform_id', $formdata->getId())->addFilter('is_active', 1)->setOrder('position','ASC');
                    
                    foreach ($fields as $field) {
                        
                        if(!$field->getId()){
                            continue;
                        }

                        if ($field->getFieldsetId()) {
                            $fieldPosition = $positionArray[$field->getFieldsetId() ];
                        }
                        
                        $field_hint = $field->getHint();
                        if ($field->getType() == 'subscribe') {
                            $field_hint = $field->getData() ['value']['newsletter_label'];
                        }

                        if ($field->getRequired() == "1") {
                            $validation_advice = $field->getvalidation_advice();

                            if ($field->getvalidation_advice() == ''){
                                $validation_advice = 'This is a required field.';
                            }

                            if ($field->getType() == 'date/dob') {
                                $validation_advice = 'Please enter a date.';
                            }
                        } else {
                            $validation_advice = '';
                        }

                        $hidden_value = '';
                        if ($field->getType() == 'hidden') {
                            $hidden_value  = !empty($field->getValue()['hidden']) ? $field->getValue()['hidden'] : '';
                        }

                        $GetFormData['webformfields'][$i]['field_id'] = $field->getId();
                        $GetFormData['webformfields'][$i]['field_label'] = $field->getName();
                        $GetFormData['webformfields'][$i]['field_label_hide'] = $field->getHideLabel();
                        $GetFormData['webformfields'][$i]['field_type'] = $field->getType();
                        $GetFormData['webformfields'][$i]['field_code'] = $field->getCode();
                        $GetFormData['webformfields'][$i]['field_hidden_value'] = $hidden_value;
                        $GetFormData['webformfields'][$i]['field_position'] = $field->getPosition();
                        $GetFormData['webformfields'][$i]['field_hint'] = $field_hint;
                        $GetFormData['webformfields'][$i]['required'] = $field->getRequired();
                        $GetFormData['webformfields'][$i]['is_active'] = $field->getIsActive();
                        $GetFormData['webformfields'][$i]['position'] =  $fieldPosition;
                        $GetFormData['webformfields'][$i]['field_width'] = trim($field->getCssClassContainer());
                        $GetFormData['webformfields'][$i]['validation_advice'] = $validation_advice;
                        if (count($field->getSelectValues()) > 0) {
                            $GetFormData['webformfields'][$i]['multiselect'] = $field->getMultiselect();
                            foreach ($field->getOptionsArray() as $select_options) {
                                $GetFormData['webformfields'][$i]['select_options'][$j]['label'] = $select_options['label'];
                                $GetFormData['webformfields'][$i]['select_options'][$j]['value'] = $select_options['value'];
                                if (isset($select_options['null']) && ($select_options['null'] == 1)) $GetFormData['webformfields'][$i]['select_options'][$j]['value'] = '';
                                if ($field->getType() == 'subscribe') {
                                    break;
                                }
                                $j++;
                            }
                            $j = 0;
                        }
                        $i++;
                    }
                }
            }
        }
        catch(\Exception $e) {
            throw new GraphQlInputException(__($e->getMessage()));
        }
        return $GetFormData;
    }

    /**
     * Generate Form Key
     *
     * @return string
     */
    public function getFormKey() {
        //$this->formKey->getFormKey();
        return uniqid();
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
}
