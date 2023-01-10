<?php
/**
 * Alpine_OrdersExport Controller
 *
 * @category    Alpine
 * @package     Alpine_OrdersExport
 * @copyright   Copyright (c) 2018 Alpine Consulting, Inc
 * @author      Dmitry Naumov <dmitry.naumov@alpineinc.com>
 * @author      Alex Didenko <alex.didenko@alpineinc.com>
 */

namespace Alpine\OrdersExport\Controller\Adminhtml\Export;

use Alpine\OrdersExport\Model\Export\Report as ModelExport;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Response\Http\FileFactory;

/**
 * Alpine\OrdersExport\Controller\Adminhtml\Export\Report2
 *
 * @category    Alpine
 * @package     Alpine_OrdersExport
 */
class Report2 extends Action
{
    /**
     * Export Model
     *
     * @var ModelExport
     */
    protected $model;

    /**
     * File Name of the report
     *
     * @var string
     */
    protected $fileName;

    /**
     * Fields of the report
     *
     * @var string
     */
    protected $fields;

    /**
     * File factory
     *
     * @var FileFactory
     */
    protected $fileFactory;

    /**
     * Constructor
     *
     * @param Context $context
     * @param ModelExport $model
     * @param FileFactory $fileFactory
     * @param string $fileName
     * @param array $fields
     */
    public function __construct(
        Context $context,
        ModelExport $model,
        FileFactory $fileFactory,
        $fileName,
        array $fields
    ) {
        $this->model = $model;
        $this->fileName = $fileName;
        $this->fields = $fields;
        $this->fileFactory = $fileFactory;
        parent::__construct($context);
    }

    /**
     * Execute
     *
     * @return \Magento\Framework\App\ResponseInterface
     */
    public function execute()
    {
        $filters = $this->getRequest()->getParam('filters');

        $content = $this->model->getContent(
            $this->fileName,
            $this->fields,
            $filters,
            true
        );

        $result = $this->fileFactory->create(
            $this->fileName,
            $content
        );

        return $result;
    }
}