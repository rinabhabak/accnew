<?php
/**
 * Indusnet Technologies.
 *
 * @category  Indusnet
 * @package   Int_AggregationGraphQl
 * @author    Indusnet
 */
declare(strict_types=1);

namespace Int\AggregationGraphQl\Model\Resolver\Category;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Catalog\Model\Category;
use Magento\Catalog\Model\ResourceModel\Category\CollectionFactory;

/**
 * Returns media url
 */
class CategoryImage implements ResolverInterface
{
    
    protected $_storeManager;

    /**
     * @var Category
     */
    private $category;

    public function __construct(
        \Magento\Store\Model\StoreManagerInterface $storemanager,
        Category $category
    ) {
        $this->_storeManager =  $storemanager;
        $this->category = $category;
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
    ) {
        

        if (!isset($value['model'])) {
            throw new LocalizedException(__('"model" value should be specified'));
        }

        /** @var Product $product */
        $product = $value['model'];
        $store = $this->_storeManager->getStore();
        $media_url = $store->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
        $category = $this->category->load($value['id']);
        
        return $category->getImageUrl();
    }

}
