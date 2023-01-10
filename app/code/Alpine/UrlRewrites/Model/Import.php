<?php
/**
 * Alpine_UrlRewrites
 *
 * @category    Alpine
 * @package     Alpine_UrlRewrites
 * @copyright   Copyright (c) 2018 Alpine Consulting, Inc
 * @author      Alex Didenko <alex.didenko@alpineinc.com>
 */

namespace Alpine\UrlRewrites\Model;

use Magento\Framework\File\Csv;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\UrlRewrite\Model\UrlRewriteFactory;
use Magento\Store\Model\StoreManagerInterface;
use Psr\Log\LoggerInterface;

/**
 * Class Import
 *
 * @category    Alpine
 * @package     Alpine_UrlRewritesCommand
 */
class Import
{
    /**
     * Directory list
     *
     * @var DirectoryList
     */
    protected $directoryList;

    /**
     * Url rewrite
     *
     * @var UrlRewriteFactory
     */
    protected $urlRewrite;

    /**
     * Store Manager
     *
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * Csv Processor
     *
     * @var Csv
     */
    protected $csv;

    /**
     * Logger
     *
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * Csv file name with url rewrite data
     *
     * @var string
     */
    protected $fileName = 'rewrites.csv';

    /**
     * Import constructor
     *
     * @param DirectoryList $directoryList
     * @param UrlRewriteFactory $rewriteFactory
     * @param StoreManagerInterface $storeManager
     * @param Csv $csv
     * @param LoggerInterface $logger
     */
    public function __construct(
        DirectoryList $directoryList,
        UrlRewriteFactory $rewriteFactory,
        StoreManagerInterface $storeManager,
        Csv $csv,
        LoggerInterface $logger
    ) {
        $this->directoryList = $directoryList;
        $this->urlRewrite = $rewriteFactory;
        $this->storeManager = $storeManager;
        $this->csv = $csv;
        $this->logger = $logger;
    }

    /**
     * Execute
     */
    public function execute()
    {
        $fileName = $this->fileName;
        $mediaPath = $this->directoryList->getPath(DirectoryList::MEDIA);
        $filePath = $mediaPath . DIRECTORY_SEPARATOR . $fileName;
        $csvData = $this->csv->getData($filePath);

        $defaultStoreId = $this->storeManager->getDefaultStoreView()->getId();
        $entityType = 'custom';
        $redirectType = 301;

        foreach ($csvData as $data) {
            $requestPath = $this->prepareRequestPath($data[0]);
            if (!$requestPath) {
                continue;
            }
            $targetPath = $this->prepareTargetPath($data[1], $requestPath);
            $urlRewrite = $this->urlRewrite->create();
            $urlRewrite->setEntityType($entityType);
            $urlRewrite->setRequestPath($requestPath);
            $urlRewrite->setTargetPath($targetPath);
            $urlRewrite->setRedirectType($redirectType);
            $urlRewrite->setStoreId($defaultStoreId);
            try {
                $urlRewrite->save();
            } catch (\Exception $e) {
                $this->logger->error($e->getMessage(), ['url_rewrite_import_data' => $data]);
            }
        }
    }

    /**
     * Prepare request path
     *
     * @param string $rawValue
     * @return null|string
     */
    protected function prepareRequestPath($rawValue)
    {
        $result = null;
        $splittedValue = explode("/", $rawValue);

        if (count($splittedValue) > 1) {
            if (in_array('en-us', $splittedValue) || in_array('en-mx', $splittedValue)) {
                $result = implode("/", $splittedValue);
            }
        }

        return $result;
    }

    /**
     * Prepare target path
     *
     * @param string $rawValue
     * @param string $requestPath
     * @return string
     */
    protected function prepareTargetPath($rawValue, $requestPath)
    {
        if ($rawValue) {
            $result = $rawValue;
        } else {
            $splittedValue = explode("/", $requestPath);
            array_shift($splittedValue);
            $result = implode("/", $splittedValue);
        }

        return $result;
    }
}
