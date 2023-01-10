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
class CheckPayerAuthEnrollment implements ResolverInterface
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

         if (!isset($args['input']['clientReferenceInformation']) && !isset($args['input']['orderInformation']) && !isset($args['input']['paymentInformation']) && !isset($args['input']['consumerAuthenticationInformation'])) 
         {
            throw new GraphQlInputException(__('"formId" value should be specified'));
         }

         $clientReferenceInformationArr = ["code" => $args['input']['clientReferenceInformation'][0]['code']];

         $clientReferenceInformation = new \CyberSource\Model\Riskv1decisionsClientReferenceInformation($clientReferenceInformationArr);

         $orderInformationAmountDetailsArr = [
            "currency" => $args['input']['orderInformation'][0]['amountDetails'][0]['currency'],
            "totalAmount" => $args['input']['orderInformation'][0]['amountDetails'][0]['totalAmount']
         ];

         $orderInformationAmountDetails = new \CyberSource\Model\Riskv1authenticationsOrderInformationAmountDetails($orderInformationAmountDetailsArr);
   
         $orderInformationBillToArr = [
            "address1" => $args['input']['orderInformation'][0]['billTo'][0]['address1'],
            "administrativeArea" => $args['input']['orderInformation'][0]['billTo'][0]['administrativeArea'],
            "country" => $args['input']['orderInformation'][0]['billTo'][0]['country'],
            "locality" => $args['input']['orderInformation'][0]['billTo'][0]['locality'],
            "firstName" => $args['input']['orderInformation'][0]['billTo'][0]['firstName'],
            "lastName" => $args['input']['orderInformation'][0]['billTo'][0]['lastName'],
            "email" => $args['input']['orderInformation'][0]['billTo'][0]['email'],
            "postalCode" => $args['input']['orderInformation'][0]['billTo'][0]['postalCode']
         ];

         $orderInformationBillTo = new \CyberSource\Model\Riskv1authenticationsOrderInformationBillTo($orderInformationBillToArr);
   
         $orderInformationArr = [
            "amountDetails" => $orderInformationAmountDetails,
            "billTo" => $orderInformationBillTo
         ];

         $orderInformation = new \CyberSource\Model\Riskv1authenticationsOrderInformation($orderInformationArr);

         $paymentInformationCustomerArr = ["customerId" => $args['input']['paymentInformation'][0]['customer'][0]['customerId']];

         $paymentInformationCustomer = new \CyberSource\Model\Riskv1authenticationsetupsPaymentInformationCustomer($paymentInformationCustomerArr);

         $paymentInformationArr = [
            "customer" => $paymentInformationCustomer
         ];
         
         $paymentInformation = new \CyberSource\Model\Riskv1authenticationsPaymentInformation($paymentInformationArr);

         $consumerAuthenticationInformationArr = [
            "referenceId" => $args['input']['consumerAuthenticationInformation'][0]['referenceId'],
            "returnUrl" => $args['input']['consumerAuthenticationInformation'][0]['returnUrl']
         ];
         $consumerAuthenticationInformation = new \CyberSource\Model\Riskv1decisionsConsumerAuthenticationInformation($consumerAuthenticationInformationArr);
         
         $requestObjArr = [
               "clientReferenceInformation" => $clientReferenceInformation,
               "orderInformation" => $orderInformation,
               "paymentInformation" => $paymentInformation,
               "consumerAuthenticationInformation" => $consumerAuthenticationInformation
         ];

         $requestObj = new \CyberSource\Model\CheckPayerAuthEnrollmentRequest($requestObjArr);

         $format = "JWT";
         $config = $this->_cyberSourceHelper->ConnectionHost();
         $merchantConfig = $this->_cyberSourceHelper->merchantConfigObject();

	      $api_client = new \CyberSource\ApiClient($config, $merchantConfig);
	      $api_instance = new \CyberSource\Api\PayerAuthenticationApi($api_client);

         try {

            $apiResponse = $api_instance->checkPayerAuthEnrollment($requestObj);

            $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/PayerAuth.log');
            $logger = new \Zend\Log\Logger();
            $logger->addWriter($writer);
            $logger->info('Check - '.print_r($apiResponse,1));

            if(isset($apiResponse[1]) && $apiResponse[1] == 201):
               $apiResponse = (array)$apiResponse[0];
		         $apiResponse = reset($apiResponse);

               $apiCardResponse = (array)$apiResponse['consumerAuthenticationInformation'];
               $apiCardResponse = reset($apiCardResponse);

               $apiErrorResponse = (array)$apiResponse['errorInformation'];
               $apiErrorResponse = reset($apiErrorResponse);
               
               $apiResponseArray= array('id' => $apiResponse['id'],'status' => $apiResponse['status'],'submitTimeUtc' => $apiResponse['submitTimeUtc'], 'authenticationTransactionId' => $apiCardResponse['authenticationTransactionId'], 'pareq' => $apiCardResponse['pareq'], 'stepUpUrl' => $apiCardResponse['stepUpUrl'], 'reason' => $apiErrorResponse['reason'], 'message' => $apiErrorResponse['message'], 'accessToken'=> $apiCardResponse['accessToken']);
               return $apiResponseArray;
            else:
               $apiResponseArray= array('id' => null,'status' => null,'submitTimeUtc' => null, 'authenticationTransactionId' => null, 'pareq' => null, 'stepUpUrl' => null, 'reason' => null, 'message' => null,'accessToken' => null);
               return $apiResponseArray;
            endif;
         } catch (\Cybersource\ApiException $e) {

         }
    }
}