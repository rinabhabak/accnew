<?php
/**
 * Indusnet Technologies.
 *
 * @category  Indusnet
 * @package   Int_CyberSource
 * @author    Indusnet
 */

namespace Int\CyberSource\Model\Resolver;

//resolver section
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Exception\GraphQlNoSuchEntityException;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Framework\GraphQl\Exception\GraphQlAuthorizationException;

/**
 * Class ConfiguratorSave
 * @package Int\ConfiguratorGraphQl\Model\Resolver
 */
class GenerateCyberSourceKey implements ResolverInterface
{
    protected $cyberSourceHelper;
    
    protected $GeneratePublicKeyRequest;

    protected $ApiClient;
    
    protected $KeyGenerationApi;
    

    public function __construct(
    \Int\CyberSource\Helper\Data $cyberSourceHelper
    ){
        
        $this->_cyberSourceHelper = $cyberSourceHelper;
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
        $requestObjArr = [
			"encryptionType" => "RsaOaep",
			"targetOrigin" => "https://www.test.com"
        ];

        $requestObj = new \CyberSource\Model\GeneratePublicKeyRequest($requestObjArr);
        $format = "JWT";
        $config = $this->_cyberSourceHelper->ConnectionHost();
        $merchantConfig = $this->_cyberSourceHelper->merchantConfigObject();

        $api_client = new \CyberSource\ApiClient($config, $merchantConfig);
        $api_instance = new \CyberSource\Api\KeyGenerationApi($api_client);

        try {
            $apiResponse = $api_instance->generatePublicKey($format, $requestObj);
            $digest      = $apiResponse[2]['Digest'];
            $date        = $apiResponse[2]['Date'];
            $apiResponse = (array)$apiResponse[0];
		    $apiResponse = reset($apiResponse);

            $generateSign = new \CyberSource\Authentication\Http\HttpSignatureGenerator($merchantConfig->getLogConfiguration());
            $sign         = str_replace("Signature:","",$generateSign->generateToken('(request-target)',$digest,'POST',$merchantConfig));

            $apiResponseArray= array('date' => $date,'digest' => $digest,'signature' => $sign, 'keyId'=>  $apiResponse['keyId']);

            return $apiResponseArray;
        } catch (Cybersource\ApiException $e) {

        }
    }
}