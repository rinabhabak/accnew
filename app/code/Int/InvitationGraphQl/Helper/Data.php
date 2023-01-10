<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Int\InvitationGraphQl\Helper;

use Magento\Invitation\Model\Invitation;
use Magento\Framework\App\Config\ScopeConfigInterface;

/**
 * Invitation data helper
 */
class Data extends \Magento\Invitation\Helper\Data
{
       const INVITATION_PWA_URL = 'customer/password/invitation_pwa_url';
       /**
        * @var ScopeConfigInterface
        */
        protected $scopeConfig;

       /**
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Customer\Model\Registration $registration
     * @param \Magento\Invitation\Model\Source\Invitation\Status $invitationStatus
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Customer\Model\Registration $registration,
        \Magento\Invitation\Model\Source\Invitation\Status $invitationStatus,
        ScopeConfigInterface $scopeConfig
        
    ) {
        parent::__construct($context,$registration,$invitationStatus);
        $this->scopeConfig = $scopeConfig;
    }


    /**
     * Return invitation url
     *
     * @param Invitation $invitation
     * @return string
     */
    public function getPwaInvitationUrl($invitation)
    {
        $invitation_pwa_url = $this->scopeConfig->getValue( self::INVITATION_PWA_URL, \Magento\Store\Model\ScopeInterface::SCOPE_STORE );
        return $invitation_pwa_url.$this->urlEncoder->encode($invitation->getInvitationCode());
    }

    
}
