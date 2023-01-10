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
class SetupPayerAuthEnrollment implements ResolverInterface
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

        if (!isset($args['input']['clientReferenceInformation']) && !isset($args['input']['paymentInformation'])) 
        {
        throw new GraphQlInputException(__('"formId" value should be specified'));
        }

        $clientReferenceInformationArr = ["code" => $args['input']['clientReferenceInformation'][0]['code']];

        $clientReferenceInformation = new \CyberSource\Model\Riskv1decisionsClientReferenceInformation($clientReferenceInformationArr);

        $paymentInformationCustomerArr = ["customerId" => $args['input']['paymentInformation'][0]['customer'][0]['customerId']];

        $paymentInformationCustomer = new \CyberSource\Model\Riskv1authenticationsetupsPaymentInformationCustomer($paymentInformationCustomerArr);

        $paymentInformationArr = ["customer" => $paymentInformationCustomer];

        $paymentInformation = new \CyberSource\Model\Riskv1authenticationsetupsPaymentInformation($paymentInformationArr);

        $requestObjArr = [
            "clientReferenceInformation" => $clientReferenceInformation,
            "paymentInformation" => $paymentInformation
        ];

        $requestObj = new \CyberSource\Model\PayerAuthSetupRequest($requestObjArr);
         
        $format = "JWT";
        $config = $this->_cyberSourceHelper->ConnectionHost();
        $merchantConfig = $this->_cyberSourceHelper->merchantConfigObject();

        $api_client = new \CyberSource\ApiClient($config, $merchantConfig);
        $api_instance = new \CyberSource\Api\PayerAuthenticationApi($api_client);

        try {

            $apiResponse = $api_instance->payerAuthSetup($requestObj);

            $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/PayerAuth.log');
            $logger = new \Zend\Log\Logger();
            $logger->addWriter($writer);
            $logger->info('Setup - '.print_r($apiResponse,1));

            if(isset($apiResponse[1]) && $apiResponse[1] == 201):
                $apiResponse = (array)$apiResponse[0];
		        $apiResponse = reset($apiResponse);

                $apiCardResponse = (array)$apiResponse['consumerAuthenticationInformation'];
                $apiCardResponse = reset($apiCardResponse);
               
                $apiResponseArray= array('id' => $apiResponse['id'],'status' => $apiResponse['status'],'submitTimeUtc' => $apiResponse['submitTimeUtc'], 'referenceId'=> $apiCardResponse['referenceId'], 'accessToken' => $apiCardResponse['accessToken'], 'deviceDataCollectionUrl' => $apiCardResponse['deviceDataCollectionUrl']);
                return $apiResponseArray;
            else:
               $apiResponseArray= array('id' => null,'status' => null,'submitTimeUtc' => null, 'authenticationPath'=> null, 'referenceId'=> null, 'accessToken' => null, 'deviceDataCollectionUrl' => null);
               return $apiResponseArray;
            endif;

        } catch (\Cybersource\ApiException $e) {
            

        }
    }
}