<?php
/**
 * Indusnet Technologies.
 *
 * @category  Indusnet
 * @package   Int_ProductAttachmentGraphQl
 * @author    Indusnet
 */

namespace Int\ProductAttachmentGraphQl\Model\Resolver\DataProvider;

use Magento\Framework\GraphQl\Exception\GraphQlNoSuchEntityException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Store\Model\StoreManagerInterface;
use Accuride\ProductAttachment\Helper\Data;

class ProductAttachment
{
    protected $_bannerFactory;
    protected $_helperData;
    protected $_productRepository;
    protected $_reviewFactory;
    protected $storeManager;
    protected $_productAttachHelperData;

    public function __construct(
        \Magento\Catalog\Model\ProductRepository $productRepository,
        \Magento\Review\Model\ReviewFactory $reviewFactory,
        StoreManagerInterface $storeManager,
        \Accuride\ProductAttachment\Helper\Data $productAttachHelperData
        )
    {
        $this->_productRepository = $productRepository;
        $this->_reviewFactory = $reviewFactory;
        $this->storeManager = $storeManager;
        $this->_productAttachHelperData = $productAttachHelperData;
        $this->storeManager->setCurrentStore(1);
    }
    
    
    /**
     * Get Product data
     * @return array
     * @throws GraphQlNoSuchEntityException
     */
    public function getProductAttachmentData($frontendAttach,$productId)
    {
        try {
            $frontendAttachArray = array();
            $i = 0;
            
            foreach($frontendAttach as $single_file)
            {
                $frontendAttachArray[$i]['file_id'] = $single_file->getFileId();
                $frontendAttachArray[$i]['attachment_type'] = $single_file->getAttachmentType();
                $frontendAttachArray[$i]['label'] = $single_file->getLabel();

                if($single_file->getAttachmentType() == '0')
                {
                    $frontendAttachArray[$i]['icon_url'] = $single_file->getIconUrl();
                    $frontendAttachArray[$i]['frontend_url'] = $single_file->getFrontendUrl();
                    $frontendAttachArray[$i]['link'] = '';
                }
                if($single_file->getAttachmentType() == '1')
                {
                    $frontendAttachArray[$i]['icon_url'] = $this->_productAttachHelperData->getLinkIcon();
                    $frontendAttachArray[$i]['frontend_url'] = $single_file->getLink();
                    $frontendAttachArray[$i]['link'] = $single_file->getLink();
                }
                
                $i++;
            }
       
        } catch (NoSuchEntityException $e) {
            throw new GraphQlNoSuchEntityException(__($e->getMessage()), $e);
        }
        return $frontendAttachArray;
    }
}
