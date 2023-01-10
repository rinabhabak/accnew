<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Storelocator
 */

/**
 * Alpine_Storelocator
 *
 * @category    Alpine
 * @package     Alpine_Storelocator
 * @copyright   Copyright (c) 2019 Alpine Consulting, Inc
 * @author      Andrey Nesterov <andrey.nesterov@alpineinc.com>
 * @author      Anton Smolenov <anton.smolenov@alpineinc.com>
 */

namespace Alpine\Storelocator\Block\Adminhtml\Location\Edit\Tab;

use Amasty\Storelocator\Block\Adminhtml\Location\Edit\Tab\General as BaseGeneral;

/**
 * Alpine\Storelocator\Block\Adminhtml\Location\Edit\Tab\General
 *
 * @category    Alpine
 * @package     Alpine_Storelocator
 */
class General extends BaseGeneral
{
    /**
     * Prepare form before rendering HTML
     *
     * @return $this
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function _prepareForm()
    {
        $model = $this->_coreRegistry->registry('current_amasty_storelocator_location');

        $yesno = $this->yesno->toOptionArray();

        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create();
        $form->setHtmlIdPrefix('location_');

        $ObjectManager = \Magento\Framework\App\ObjectManager::getInstance();

        $fieldset = $form->addFieldset('base_fieldset', ['legend' => __('General')]);
        if ($model->getId()) {
            $fieldset->addField('id', 'hidden', ['name' => 'id']);
        }

        $fieldset->addField(
            'name',
            'text',
            [
                'label'    => __('Location name'),
                'required' => true,
                'name'     => 'name',
            ]
        );


        if (!$this->_storeManager->isSingleStoreMode()) {
            $fieldset->addField(
                'stores',
                'multiselect',
                [
                    'name'     => 'stores[]',
                    'label'    => __('Store View'),
                    'title'    => __('Store View'),
                    'required' => true,
                    'values'   => $this->_store->getStoreValuesForForm(false, true)
                ]
            );
        } else {
            $fieldset->addField(
                'store_id',
                'hidden',
                [
                    'name'  => 'store_id[]',
                    'value' => $this->_storeManager->getStore(true)->getId()
                ]
            );
        }

        $fieldset->addField(
            'country',
            'select',
            [
                'name'     => 'country',
                'required' => true,
                'class'    => 'countries',
                'label'    => 'Country',
                'values'   => $ObjectManager->get('Magento\Config\Model\Config\Source\Locale\Country')->toOptionArray(),
            ]
        );

        $fieldset->addField(
            'state_id',
            'select',
            [
                'name'  => 'state_id',
                'label' => 'State/Province',
            ]
        );

        $fieldset->addField(
            'state',
            'hidden',
            [
                'name'  => 'state',
                'label' => 'State/Province',

            ]
        );

        $fieldset->addField(
            'city',
            'text',
            [
                'label'    => __('City'),
                'required' => false,
                'name'     => 'city',
            ]
        );

        $fieldset->addField(
            'description',
            'editor',
            [
                'label'    => __('Description'),
                'required' => false,
                'config'   => $this->_wysiwygConfig->getConfig(),
                'name'     => 'description',
            ]
        );

        $fieldset->addField(
            'zip',
            'text',
            [
                'label'    => __('Zip'),
                'required' => false,
                'name'     => 'zip',
            ]
        );

        $fieldset->addField(
            'address',
            'text',
            [
                'label'    => __('Address'),
                'required' => false,
                'name'     => 'address',
            ]
        );

        $fieldset->addField(
            'phone',
            'text',
            [
                'label' => __('Phone Number'),
                'name'  => 'phone',
            ]
        );

        $fieldset->addField(
            'email',
            'text',
            [
                'label' => __('E-mail Address'),
                'name'  => 'email',
            ]
        );

        $fieldset->addField(
            'website',
            'text',
            [
                'label' => __('Website URL'),
                'name'  => 'website',
            ]
        );

        $fieldset->addField(
            'status',
            'select',
            [
                'label'    => __('Status'),
                'required' => true,
                'name'     => 'status',
                'values'   => ['1' => 'Enabled', '0' => 'Disabled'],
            ]
        );

        $fieldset->addField(
            'show_schedule',
            'select',
            [
                'label'    => __('Show Schedule'),
                'required' => false,
                'name'     => 'show_schedule',
                'values'   => $yesno,
            ]
        );

        $fieldset->addField(
            'position',
            'text',
            [
                'class'    => 'validate-number',
                'label'    => __('Position'),
                'required' => false,
                'name'     => 'position',
            ]
        );

        $fieldset->addField(
            'store_img',
            'file',
            [
                'label'              => __('Image'),
                'name'               => 'store_img',
                'after_element_html' => $this->getImageHtml('store_img', $model->getStoreImg()),
            ]
        );

        $form->setValues($model->getData());
        $form->addValues(['id' => $model->getId()]);
        $this->setForm($form);

        return $this;
    }
}
