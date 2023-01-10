<?php
/**
 * Indusnet Technologies.
 *
 * @category  Indusnet
 * @package   Int_AggregationGraphQl
 * @author    Indusnet
 */
declare(strict_types=1);

namespace Int\AggregationGraphQl\Model\Resolver\Product;

use Magento\CatalogGraphQl\Model\Resolver\Product\Price\Discount;
use Magento\CatalogGraphQl\Model\Resolver\Product\Price\ProviderPool as PriceProviderPool;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Catalog\Model\Product;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Pricing\SaleableInterface;
use Magento\Store\Api\Data\StoreInterface;

/**
 * Format product's Logos information for uom field
 */
class ProductLogos implements ResolverInterface
{
    protected $assetRepository;
    protected $request;
    protected $appEmulation;

    public function __construct(
        \Magento\Framework\View\Asset\Repository $assetRepository,
        \Magento\Framework\App\RequestInterface $request,
        \Magento\Store\Model\App\Emulation $appEmulation
    ){
        $this->assetRepository = $assetRepository;
        $this->request = $request;
        $this->appEmulation = $appEmulation;
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
    ){
        if (!isset($value['model'])) {
            throw new LocalizedException(__('"model" value should be specified'));
        }
        /** @var StoreInterface $store */
        $store = $context->getExtensionAttributes()->getStore();
        $productLogos = [];
        $logoUrl = [];

        $this->appEmulation->startEnvironmentEmulation($store->getId(), \Magento\Framework\App\Area::AREA_FRONTEND, true);
        $params = array('_secure' => $this->request->isSecure());

        if(isset($value['rohs']) && $value['rohs'] == 1){
            $logoUrl[] = $this->assetRepository->getUrlWithParams('images/cms/logos/rohs.png', $params);
        }
        if(isset($value['bhma']) && $value['bhma'] == 1){
            $logoUrl[] = $this->assetRepository->getUrlWithParams('images/cms/logos/bhma-new.png', $params);
        }
        if(isset($value['awi']) &&  $value['awi'] == 1){
            $logoUrl[] = $this->assetRepository->getUrlWithParams('images/cms/logos/awi-new.png', $params);
        }

        foreach($logoUrl as $logourl){
            $productLogos[]['logo_url'] = $logourl;
        }

        return $productLogos;
    }

}
