<?php
/**
 * Indusnet Technologies.
 *
 * @category  Indusnet
 * @package   Int_HomeBannerGraphQl
 * @author    Indusnet
 */
namespace Int\HomeBannerGraphQl\Model\Resolver\DataProvider;

use Magento\Framework\GraphQl\Exception\GraphQlNoSuchEntityException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Store\Model\StoreManagerInterface;

class NewProducts
{
    protected $_bannerFactory;
    protected $_helperData;
    protected $_productRepository;
    protected $_reviewFactory;
    protected $storeManager;
    
    /**
     * @var \Magento\Catalog\Helper\Image
     */
    protected $_catalogImageHelper;

    public function __construct(
        \Magento\Catalog\Model\ProductRepository $productRepository,
        \Magento\Review\Model\ReviewFactory $reviewFactory,
        \Int\HomeBanner\Helper\Data $helperData,
        StoreManagerInterface $storeManager,
        \Magento\Catalog\Helper\Image $catalogImageHelper
        )
    {
        $this->_productRepository = $productRepository;
        $this->_reviewFactory = $reviewFactory;
        $this->_helperData  = $helperData;
        $this->storeManager = $storeManager;
        $this->storeManager->setCurrentStore(1);
        $this->_catalogImageHelper = $catalogImageHelper;
    }
    
    
    /**
     * Get Product data
     * @return array
     * @throws GraphQlNoSuchEntityException
     */
    public function getBannerData($args)
    {
        try {
            $new_product_sku = explode(',',$this->_helperData->getGeneralConfig('new_product_sku')); /* get  Products SKUs from system configuration */
            $productDataArray = array();
            $i = 0;
            $store = $this->storeManager->getStore();
            $media_url = $store->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
            foreach($new_product_sku as $sku)
            {
                
                $width = $args['product_image_width'] ?? null;
                $height = $args['product_image_height'] ?? null;
                
               $_product = $this->_productRepository->get(trim($sku));
               
               $imageUrl = $this->_catalogImageHelper->init($_product, 'product_page_image_large')
                        ->setImageFile($_product->getThumbnail()) // image,small_image,thumbnail
                        ->resize($width,$height)
                        ->getUrl();
               
                $productDataArray[$i]['prod_id'] = $_product->getEntityId();
                $productDataArray[$i]['prod_sku'] = $_product->getSku();
                $productDataArray[$i]['prod_name'] = $_product->getName();
                $productDataArray[$i]['prod_image'] = $imageUrl;
                $this->_reviewFactory->create()->getEntitySummary($_product, $this->storeManager->getStore()->getId());
                $product_rating = $_product->getRatingSummary()->getRatingSummary();
                if(empty($product_rating))
                    $product_rating = 0;
                $productDataArray[$i]['prod_rating'] = $product_rating;
                $productDataArray[$i]['prod_url'] = $_product->getUrlKey();

                $i++;
            }
            
        } catch (NoSuchEntityException $e) {
            throw new GraphQlNoSuchEntityException(__($e->getMessage()), $e);
        }
        return $productDataArray;
    }
}
