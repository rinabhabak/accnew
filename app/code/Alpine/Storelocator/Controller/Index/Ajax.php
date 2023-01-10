<?php
/**
 * Alpine_Storelocator
 *
 * @category    Alpine
 * @package     Alpine_Storelocator
 * @copyright   Copyright (c) 2019 Alpine Consulting, Inc
 * @author      Andrey Nesterov <andrey.nesterov@alpineinc.com>
 */

namespace Alpine\Storelocator\Controller\Index;

use Alpine\Storelocator\Model\Attribute;
use Amasty\Storelocator\Controller\Index\Ajax as BaseAjax;
use Amasty\Storelocator\Helper\Data;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Json\EncoderInterface;
use Magento\Framework\Registry;

/**
 * Alpine\Storelocator\Controller\Index\Ajax
 *
 * @category    Alpine
 * @package     Alpine_Storelocator
 */
class Ajax extends BaseAjax
{
    /**
     * Helper
     *
     * @var Data
     */
    protected $dataHelper;

    /**
     * Attribute model
     *
     * @var Attribute
     */
    protected $attributeModel;

    /**
     * Registry
     *
     * @var Registry
     */
    protected $registry;

    /**
     * Constructor
     *
     * @param Context $context
     * @param Data $dataHelper
     * @param EncoderInterface $jsonEncoder
     * @param Attribute $attributeModel
     * @param Registry $registry
     */
    public function __construct(
        Context $context,
        Data $dataHelper,
        EncoderInterface $jsonEncoder,
        Attribute $attributeModel,
        Registry $registry
    ) {
        parent::__construct(
            $context
            // $dataHelper,
            // $jsonEncoder
        );

        $this->dataHelper = $dataHelper;
        $this->attributeModel = $attributeModel;
        $this->registry = $registry;
    }

    /**
     * Default customer account page
     *
     * @return void
     */
    public function execute()
    {
        $locationCollection = $this->_objectManager->get('Amasty\Storelocator\Model\Location')->getCollection();

        $locationCollection->applyDefaultFilters();

        $productId = $this->getRequest()->getParam('product');

        $product = false;

        if ($productId) {
            $product = $this->_objectManager->get('Magento\Catalog\Model\Product')->load($productId);
        }

        if ($industry = $this->getRequest()->getParam('industry')) {
            $data = [
                $this->attributeModel->getAttributeIdByCode(Attribute::INDUSTRY_CODE) => $industry
            ];

            $locationCollection->applyAttributeFilters($data);
        }

        $locationCollection->load();

        $this->registry->register('amlocator_location', $locationCollection);

        $this->_view->loadLayout();
        $left = $this->_view->getLayout()->getBlock('amlocatorAjax')->toHtml();

        $arrayCollection = [];

        foreach ($locationCollection as $item) {
            if ($product) {
                $valid = $this->dataHelper->validateLocation($item, $product);
                if (!$valid) {
                    continue;
                }
            }
            $arrayCollection['items'][] = $item->getData();
        }

        $arrayCollection['totalRecords'] = isset($arrayCollection['items']) ? count($arrayCollection['items']) : 0;

        $res = array_merge_recursive(
            $arrayCollection,
            array('block' => $left)
        );

        $json = $this->_jsonEncoder->encode($res);

        $this->getResponse()->setBody($json);
    }
}
