<?php
/**
 * Magedelight
 * Copyright (C) 2019 Magedelight <info@magedelight.com>
 *
 * @category Magedelight
 * @package Magedelight_Cybersource
 * @copyright Copyright (c) 2019 Mage Delight (http://www.magedelight.com/)
 * @license http://opensource.org/licenses/gpl-3.0.html GNU General Public License,version 3 (GPL-3.0)
 * @author Magedelight <info@magedelight.com>
 */
namespace Magedelight\Cybersource\Model\Api;

class SoapClient extends \SoapClient
{
    private $merchantId;

    private $transactionKey;

    protected $_configModel;

    public function __construct(
        $configModel
    ) {
        $this->_configModel = $configModel;
        $options = array();
        $propertiesWsdl = $this->getConfigModel()->getGatewayUrl();
        parent::__construct($propertiesWsdl, $options);
        $this->merchantId = $this->getConfigModel()->getMerchantId();
        $this->transactionKey = ''.$this->getConfigModel()->getTransKey().'';
        $nameSpace = 'http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-secext-1.0.xsd';

        $soapUsername = new \SoapVar(
            $this->merchantId,
            XSD_STRING,
            null,
            $nameSpace,
            null,
            $nameSpace
        );
        $soapPassword = new \SoapVar(
            $this->transactionKey,
            XSD_STRING,
            null,
            $nameSpace,
            null,
            $nameSpace
        );
        $auth = new \stdClass();
        $auth->Username = $soapUsername;
        $auth->Password = $soapPassword;
        $soapAuth = new \SoapVar(
            $auth,
            SOAP_ENC_OBJECT,
            null, $nameSpace,
            'UsernameToken',
            $nameSpace
        );
        $token = new \stdClass();
        $token->UsernameToken = $soapAuth;
        $soapToken = new \SoapVar(
            $token,
            SOAP_ENC_OBJECT,
            null,
            $nameSpace,
            'UsernameToken',
            $nameSpace
        );
        $security = new \SoapVar(
            $soapToken,
            SOAP_ENC_OBJECT,
            null,
            $nameSpace,
            'Security',
            $nameSpace
        );

        $header = new \SoapHeader($nameSpace, 'Security', $security, true);

        $this->__setSoapHeaders(array($header));
    }

    /**
     * @return string The client's merchant ID.
     */
    public function getMerchantId()
    {
        return $this->merchantId;
    }

    /**
     * @return string The client's transaction key.
     */
    public function getTransactionKey()
    {
        return $this->transactionKey;
    }

    public function getConfigModel()
    {
        return $this->_configModel;
    }

    public function createRequest($merchantReferenceCode)
    {
        $request = new stdClass();
        $request->merchantID = $this->merchantId;
        $request->merchantReferenceCode = $merchantReferenceCode;
        $request->clientLibrary = 'CyberSource PHP 1.0.0';
        $request->clientLibraryVersion = phpversion();
        $request->clientEnvironment = php_uname();

        return $request;
    }
}
