<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Int\InvitationGraphQl\Model;

use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\UrlInterface;
//use Magento\Invitation\Model\Invitation;
/**
 * Invitation data model
 *
 * @api
 * @method int getCustomerId()
 * @method \Magento\Invitation\Model\Invitation setCustomerId(int $value)
 * @method string getInvitationDate()
 * @method \Magento\Invitation\Model\Invitation setInvitationDate(string $value)
 * @method string getEmail()
 * @method \Magento\Invitation\Model\Invitation setEmail(string $value)
 * @method int getReferralId()
 * @method \Magento\Invitation\Model\Invitation setReferralId(int $value)
 * @method string getProtectionCode()
 * @method \Magento\Invitation\Model\Invitation setProtectionCode(string $value)
 * @method string getSignupDate()
 * @method \Magento\Invitation\Model\Invitation setSignupDate(string $value)
 * @method \Magento\Invitation\Model\Invitation setStoreId(int $value)
 * @method int getGroupId()
 * @method \Magento\Invitation\Model\Invitation setGroupId(int $value)
 * @method string getMessage()
 * @method \Magento\Invitation\Model\Invitation setMessage(string $value)
 * @method string getStatus()
 * @method \Magento\Invitation\Model\Invitation setStatus(string $value)
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @since 100.0.2
 */
class Invitation extends \Magento\Invitation\Model\Invitation
{
    const XML_PATH_EMAIL_IDENTITY = 'magento_invitation/email/identity';

    const XML_PATH_EMAIL_TEMPLATE = 'customer/password/invitation_template_pwa';





    /**
     * @var array
     */
    private static $_customerExistsLookup = [];

    /**
     * @var string
     */
    protected $_eventPrefix = 'magento_invitation';

    /**
     * @var string
     */
    protected $_eventObject = 'invitation';

    /**
     * Invitation data
     *
     * @var \Magento\Invitation\Helper\Data
     */
    protected $_invitationData;

    /**
     * Store manager
     *
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * Invitation Config
     *
     * @var \Magento\Invitation\Model\Config
     */
    protected $_config;

    /**
     * Invitation History Factory
     *
     * @var \Magento\Invitation\Model\Invitation\HistoryFactory
     */
    protected $_historyFactory;

    /**
     * Customer Factory
     *
     * @var \Magento\Customer\Model\CustomerFactory
     */
    protected $_customerFactory;

    /**
     * @var \Magento\Framework\Mail\Template\TransportBuilder
     */
    protected $_transportBuilder;

    /**
     * @var \Magento\Framework\Math\Random
     */
    protected $mathRandom;

    /**
     * @var \Magento\Framework\Stdlib\DateTime
     */
    protected $dateTime;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $_scopeConfig;

    /**
     * @var Invitation\Status
     */
    protected $status;

    /**
     * @var \Magento\Customer\Api\CustomerRepositoryInterface
     */
    private $customerRepository;

    /**
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Invitation\Helper\Data $invitationData
     * @param \Magento\Invitation\Model\ResourceModel\Invitation $resource
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Invitation\Model\Config $config
     * @param \Magento\Invitation\Model\Invitation\HistoryFactory $historyFactory
     * @param \Magento\Customer\Model\CustomerFactory $customerFactory
     * @param \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder
     * @param \Magento\Framework\Math\Random $mathRandom
     * @param \Magento\Framework\Stdlib\DateTime $dateTime
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param Invitation\Status $status
     * @param \Magento\Framework\Data\Collection\AbstractDb $resourceCollection
     * @param array $data
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
   
    
  
   
    /**
     * Send invitation email
     *
     * @return bool
     */
    public function sendPwaInvitationEmail()
    {
        //echo $this->_invitationData->getPwaInvitationUrl($this);
        //echo $this->getInvitationCode();
        //$invitation->getInvitationCode();
        //echo 'local';
        //die;
        $this->makeSureCanBeSent();
        $store = $this->_storeManager->getStore($this->getStoreId());

        $templateIdentifier = $this->_scopeConfig->getValue(
            self::XML_PATH_EMAIL_TEMPLATE,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $store
        );
        $from = $this->_scopeConfig->getValue(
            self::XML_PATH_EMAIL_IDENTITY,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $store
        );
        
        $this->_transportBuilder->setTemplateIdentifier(
            $templateIdentifier
        )->setTemplateOptions(
            ['area' => \Magento\Framework\App\Area::AREA_FRONTEND, 'store' => $this->getStoreId()]
        )->setTemplateVars(
            [
                'url' => $this->_invitationData->getPwaInvitationUrl($this),
                'message' => $this->getMessage(),
                'store' => $store,
                'store_name' => $store->getGroup()->getName(),
                'inviter_name' => $this->getInviter() ? $this->getInviter()->getName() : null,
            ]
        )->setFrom(
            $from
        )->addTo(
            $this->getEmail()
        );
        $transport = $this->_transportBuilder->getTransport();
        try {
            $transport->sendMessage();
        } catch (\Magento\Framework\Exception\MailException $e) {
            return false;
        }
        $this->setStatus(\Magento\Invitation\Model\Invitation\Status::STATUS_SENT)->setUpdateDate(true)->save();
        return true;
    }


   
}
