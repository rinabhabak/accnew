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
class ValidatePayerAuthEnrollment implements ResolverInterface
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

         if (!isset($args['input']['consumerAuthenticationInformation'])) 
         {
            throw new GraphQlInputException(__('"formId" value should be specified'));
         }
      
         $consumerAuthenticationInformationArr = [
               "authenticationTransactionId" => $args['input']['consumerAuthenticationInformation'][0]['authenticationTransactionId']
         ];
         $consumerAuthenticationInformation = new \CyberSource\Model\Riskv1authenticationresultsConsumerAuthenticationInformation($consumerAuthenticationInformationArr);
      
         $requestObjArr = [
               "consumerAuthenticationInformation" => $consumerAuthenticationInformation
         ];
         $requestObj = new \CyberSource\Model\ValidateRequest($requestObjArr);

         $format = "JWT";
         $config = $this->_cyberSourceHelper->ConnectionHost();
         $merchantConfig = $this->_cyberSourceHelper->merchantConfigObject();

	      $api_client = new \CyberSource\ApiClient($config, $merchantConfig);
	      $api_instance = new \CyberSource\Api\PayerAuthenticationApi($api_client);

         try {

            $apiResponse = $api_instance->validateAuthenticationResults($requestObj);

            $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/PayerAuth.log');
            $logger = new \Zend\Log\Logger();
            $logger->addWriter($writer);
            $logger->info('Validate - '.print_r($apiResponse,1));

            if(isset($apiResponse[1]) && $apiResponse[1] == 201):
               $apiResponse = (array)$apiResponse[0];
		         $apiResponse = reset($apiResponse);

               $apiCardResponse = (array)$apiResponse['consumerAuthenticationInformation'];
               $apiCardResponse = reset($apiCardResponse);

               $apiErrorResponse = (array)$apiResponse['errorInformation'];
               $apiErrorResponse = reset($apiErrorResponse);

               $errorMessage     = '';
               if($apiResponse['status'] != 'AUTHENTICATION_SUCCESSFUL')
               {
                  $errorMessage  = $apiErrorResponse['message'];
               }

               
               $apiResponseArray= array('id' => $apiResponse['id'],'status' => $apiResponse['status'], 'authenticationStatusMsg' => $apiCardResponse['authenticationStatusMsg'], 'submitTimeUtc' => $apiResponse['submitTimeUtc'], 'message' => $errorMessage);
               return $apiResponseArray;
            else:
               $apiResponseArray= array('id' => null,'status' => null, 'authenticationStatusMsg' => null, 'submitTimeUtc' => null, 'message' => null);
               return $apiResponseArray;
            endif;
         } catch (\Cybersource\ApiException $e) {

         }
    }
}