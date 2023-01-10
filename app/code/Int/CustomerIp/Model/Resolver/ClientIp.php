<?php
/**
 * Indusnet Technologies.
 *
 * @category  Indusnet
 * @package   Int_CustomerIp
 * @author    Indusnet
 */

namespace Int\CustomerIp\Model\Resolver;

use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Exception\GraphQlNoSuchEntityException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Framework\HTTP\PhpEnvironment\RemoteAddress;

/**
 * Class ClientIp
 * @package Int\CustomerIp\Model\Resolver
 */
class ClientIp implements ResolverInterface
{
    protected $remoteAddress;

    /**
     * @var \Magento\Framework\HTTP\PhpEnvironment\Request
     */
    protected $_httpRequest;

    protected $geolocation;

    protected $countryLists;

    protected $resolver;

    public function __construct(
        \Magento\Company\Api\CompanyRepositoryInterface $companyRepository,
        RemoteAddress $remoteAddress,
        \Magento\Framework\HTTP\PhpEnvironment\Request $httpRequest,
        \Amasty\Geoip\Model\Geolocation $geolocation,
        \Magento\Framework\Locale\ListsInterface $countryLists,
        \Magento\Framework\Locale\Resolver $resolver
    ) {
        $this->companyRepository = $companyRepository;
        $this->remoteAddress = $remoteAddress;
        $this->_httpRequest = $httpRequest;
        $this->geolocation = $geolocation;
        $this->countryLists = $countryLists;
        $this->resolver = $resolver;
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

        $ip = array();
        $remoteIp = $this->remoteAddress->getRemoteAddress();
        $ip['ip'] = $remoteIp;

        $location = $this->geolocation->locate($remoteIp);

        if ($location->getCountry()) {
            $ip['code'] = $location->getCountry();
            $ip['name'] = $this->countryLists->getCountryTranslation($location->getCountry());
        }

        $ip['defaultLocale'] = strstr($this->resolver->getLocale(), '_', true);


        return $ip;

    }

}