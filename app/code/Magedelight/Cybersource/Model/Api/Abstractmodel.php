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

class Abstractmodel extends \Magento\Framework\DataObject
{
    protected $_configModel;

    protected $_inputData = array();

    protected $_responseData = array();

    protected $_merchantId;
    protected $_transKey;
    protected $_apiGatewayUrl;
    protected $_cvvEnabled;
    protected $_additionalfield;
    protected $_additionalfield1;
    protected $_additionalfield2;
    protected $_additionalfield3;
    protected $_additionalfield4;
    protected $_additionalfield5;
    protected $_additionalfield6;
    protected $_additionalfield7;
    protected $_cardCode = array(
        'VI' => '001',
        'MC' => '002',
        'AE' => '003',
        'DI' => '004',
        'JCB' => '007',
        'DC' => '005',
        'MAESTRO' => '042',
        'SWITCH' => '024',
    );

    public function __construct(
        \Magedelight\Cybersource\Model\Config $configModel
    ) {
        $this->_configModel = $configModel;
        $this->_merchantId = $this->_configModel->getMerchantId();
        $this->_transKey = $this->_configModel->getTransKey();
        $this->_apiGatewayUrl = $this->_configModel->getGatewayUrl();
        $this->_cvvEnabled = $this->_configModel->isCardVerificationEnabled();
        $this->_additionalfield = $this->_configModel->getAdditonalFieldActive();
        $this->_additionalfield1 = $this->_configModel->getAdditonalField1();
        $this->_additionalfield2 = $this->_configModel->getAdditonalField2();
        $this->_additionalfield3 = $this->_configModel->getAdditonalField3();
        $this->_additionalfield4 = $this->_configModel->getAdditonalField4();
        $this->_additionalfield5 = $this->_configModel->getAdditonalField5();
        $this->_additionalfield6 = $this->_configModel->getAdditonalField6();
        $this->_additionalfield7 = $this->_configModel->getAdditonalField7();
    }

    public function setInputData($input = null)
    {
        $this->_inputData = $input;

        return $this;
    }

    public function getInputData()
    {
        return $this->_inputData;
    }

    public function setResponseData($response = array())
    {
        $this->_responseData = $response;

        return $this;
    }

    public function getResponseData()
    {
        return $this->_responseData;
    }

    public function getConfigModel()
    {
        return $this->_configModel;
    }
}
