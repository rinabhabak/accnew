<?php
/**
 * Indusnet Technologies.
 *
 * @category  Indusnet
 * @package   Int_ProductAttachmentGraphQl
 * @author    Indusnet
 */

namespace Int\ProductAttachmentGraphQl\Model\Override;
use Amasty\ProductAttachmentApi\Api\FrontendAttachmentInterface;
use Amasty\ProductAttachment\Controller\Adminhtml\RegistryConstants;
use Amasty\ProductAttachment\Model\File\FileScope\FileScopeDataProviderInterface;
use Amasty\ProductAttachment\Model\SourceOptions\OrderFilterType;
use Magento\Store\Model\StoreManagerInterface;

class FrontendAttachment extends  \Amasty\ProductAttachmentApi\Model\FrontendAttachment
{
    /**
     * @var StoreManagerInterface
     */
    public $storeManager;
    public $fileScopeDataProvider;
      /**
     * @var File\FrontendFileFactory
     */
    public $frontendFileFactory;

    public function __construct(
        FileScopeDataProviderInterface $fileScopeDataProvider,
        \Amasty\ProductAttachmentApi\Model\File\FrontendFileFactory $frontendFileFactory,
        StoreManagerInterface $storeManager
    ) {
        $this->fileScopeDataProvider = $fileScopeDataProvider;
        $this->params = [];
        $this->frontendFileFactory = $frontendFileFactory;
        $this->storeManager = $storeManager;
    }


   
public function getByProductId(
        $productId,
        $extraUrlParams = [],
        $includeInOrderOnly = false,
        $amastyCustomerGroup = 0
    ) {
        $this->params[RegistryConstants::CUSTOMER_GROUP] = $amastyCustomerGroup;
        $this->params[RegistryConstants::PRODUCT] = $productId;
        $this->params[RegistryConstants::STORE] = $this->storeManager->getStore()->getId();

        if ($includeInOrderOnly) {
            $this->params[RegistryConstants::INCLUDE_FILTER] = OrderFilterType::INCLUDE_IN_ORDER_ONLY;
        }

        if (!empty($extraUrlParams)) {
            $this->params[RegistryConstants::EXTRA_URL_PARAMS] = $extraUrlParams;
        }
        return $this->processAttachments(
            $this->fileScopeDataProvider->execute(
                $this->params,
                'frontendProduct'
            )
        );
    }
    

    
   

    /**
     * @param \Amasty\ProductAttachmentApi\Api\Data\FrontendFileInterface[] $attachments
     *
     * @return \Amasty\ProductAttachmentApi\Api\Data\FrontendFileInterface[]
     */
    public function processAttachments($attachments)
    {
        $result = [];
        
        /** @var \Amasty\ProductAttachment\Api\Data\FileInterface $file */
        foreach ($attachments as $file) {
            /** @var File\FrontendFile $frontEndFile */
            $frontEndFile = $this->frontendFileFactory->create();

            $frontEndFile->setData($file->getData());
            $frontEndFile->setUrl($file->getFrontendUrl());
            $result[] = $frontEndFile;
        }

        return $result;
    }
}
