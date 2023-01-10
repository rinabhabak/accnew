<?php
/**
 * @author Int Team
 * @copyright Copyright (c) 2020 Indus Net Technologies
 */


namespace Int\ProductAttachmentGraphQl\Model\Override\File\FileScope\DataProviders\Frontend;

use Amasty\ProductAttachment\Api\Data\FileInterface;
use Amasty\ProductAttachment\Controller\Adminhtml\RegistryConstants;
use Amasty\ProductAttachment\Model\ConfigProvider;
use Amasty\ProductAttachment\Model\File\FileScope\ResourceModel\FileStore;
use Amasty\ProductAttachment\Model\Icon\GetIconForFile;
use Amasty\ProductAttachment\Model\SourceOptions\OrderFilterType;
use Amasty\ProductAttachment\Model\SourceOptions\UrlType;
use Magento\Customer\Model\Session;
use Magento\Framework\Url;

class File extends \Amasty\ProductAttachment\Model\File\FileScope\DataProviders\Frontend\File
{
	/**
     * @var FileStore
     */
    protected $fileStore;

    /**
     * @var GetIconForFile
     */
    protected $getIconForFile;

    /**
     * @var Url
     */
    protected $urlBuilder;

    /**
     * @var Session
     */
    protected $customerSession;

    /**
     * @var ConfigProvider
     */
    protected $configProvider;

    public function __construct(
        FileStore $fileStore,
        GetIconForFile $getIconForFile,
        Session $customerSession,
        Url $urlBuilder,
        ConfigProvider $configProvider
    ) {
        $this->fileStore = $fileStore;
        $this->getIconForFile = $getIconForFile;
        $this->urlBuilder = $urlBuilder;
        $this->customerSession = $customerSession;
        $this->configProvider = $configProvider;
    }

	/**
     * @param FileInterface $file
     * @param $params
     *
     * @return bool|FileInterface
     */
    public function processFileParams(FileInterface $file, $params)
    {
        if (!$file->isVisible()) {
            return false;
        }

        if (isset($params[RegistryConstants::INCLUDE_FILTER])) {
            switch ($params[RegistryConstants::INCLUDE_FILTER]) {
                case OrderFilterType::INCLUDE_IN_ORDER_ONLY:
                    if (!$file->isIncludeInOrder()) {
                        return false;
                    }
                    break;
            }
        } else {
            if ($this->configProvider->excludeIncludeInOrderFiles() && $file->isIncludeInOrder()) {
                return false;
            }
        }
        if ($customerGroups = $file->getCustomerGroups()) {
            if (isset($params[RegistryConstants::CUSTOMER_GROUP])) {
                /** through api */
                if (!in_array($params[RegistryConstants::CUSTOMER_GROUP], $customerGroups)) {
                    return false;
                }
            } else {
                if (!in_array($this->customerSession->getCustomerGroupId(), $customerGroups)) {
                    return false;
                }
            }
        }

        $file->setIconUrl($this->getIconForFile->byFileExtension($file->getFileExtension()));

        $extraUrlParams = !empty($params[RegistryConstants::EXTRA_URL_PARAMS])
            ? $params[RegistryConstants::EXTRA_URL_PARAMS]
            : [];

        $mediaPath = $this->getMediafile($file->getFilepath());

        if($mediaPath === false)
        {
            $mediaPath = $this->urlBuilder->setScope(0)->getUrl(
                'amfile/file/download',
                array_merge([
                    'file' => $this->configProvider->getUrlType() === UrlType::ID ? $file->getFileId()
                        : $file->getUrlHash(),
                    '_nosid' => true,
                ], $extraUrlParams)
            );
        }
        
        $file->setFrontendUrl($mediaPath);

        return $file;
    }

    private function getMediafile($filePath)
    {
        if(empty($filePath)){
            return false;
        }

        // MEDIA URL 
        $mediaUrl = $this->urlBuilder->getBaseUrl(['_type' => \Magento\Framework\UrlInterface::URL_TYPE_MEDIA]);;

        $file_url = $mediaUrl. "amasty/amfile/attach/" .$filePath;

        if (!$this->check_file_exists_here($file_url)) {   
            return false;
        } else {
            return $file_url;
        }
    }

    private function check_file_exists_here($url){
        $result = get_headers($url);
        return stripos($result[0],"200 OK")?true:false;
    }
}
