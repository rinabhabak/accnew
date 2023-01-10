<?php

namespace Int\Company\Plugin\Email;

use Magento\Company\Api\CompanyRepositoryInterface;
use Magento\Company\Model\Config\EmailTemplate as EmailTemplateConfig;
use Magento\Company\Model\Email\CustomerData;
use Magento\Company\Model\Email\Transporter;
use Magento\Customer\Api\CustomerNameGenerationInterface;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Escaper;
use Magento\Framework\Exception\MailException;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;

class Sender
{

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var \Magento\Company\Model\Email\Transporter
     */
    private $transporter;

    /**
     * @var \Magento\Company\Model\Config\EmailTemplate
     */
    private $emailTemplateConfig;

    private $escaper;

    /**
     * @param ScopeConfigInterface $scopeConfig
     * @param Transporter $transporter
     * @param EmailTemplateConfig $emailTemplateConfig
     */
    public function __construct(
        StoreManagerInterface $storeManager,
        ScopeConfigInterface $scopeConfig,
        \Magento\Framework\Mail\Template\TransportBuilder $transporter,
        EmailTemplateConfig $emailTemplateConfig,
        Escaper $escaper
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->transporter = $transporter;
        $this->emailTemplateConfig = $emailTemplateConfig;
        $this->storeManager = $storeManager;
        $this->escaper = $escaper;
    }


    public function aroundSendAdminNotificationEmail($subject, $proceed, CustomerInterface $customer, $companyName, $companyUrl)
    {
        $toCode = $this->emailTemplateConfig->getCompanyCreateRecipient(ScopeInterface::SCOPE_STORE);
        $toEmail = $this->scopeConfig->getValue('trans_email/ident_' . $toCode . '/email', ScopeInterface::SCOPE_STORE);
        $toName = $this->scopeConfig->getValue('trans_email/ident_' . $toCode . '/name', ScopeInterface::SCOPE_STORE);

        $copyTo = $this->emailTemplateConfig->getCompanyCreateCopyTo(ScopeInterface::SCOPE_STORE);
        $copyMethod = $this->emailTemplateConfig->getCompanyCreateCopyMethod(ScopeInterface::SCOPE_STORE);
        $storeId = $customer->getStoreId() ?: $this->getWebsiteStoreId($customer);

        $sendTo = [];
        if ($copyTo && $copyMethod == 'copy') {
            $sendTo = explode(',', $copyTo);
        }
        array_unshift($sendTo, $toEmail);

        foreach ($sendTo as $recipient) {

            $templateParams = array_merge(
                [
                    'customer' => $customer->getFirstname(),
                    'company' => $companyName,
                    'admin' => $toName,
                    'company_url' => $companyUrl
                ],
                ['escaper' => $this->escaper]
            );

            $sender = array(
                'name' => $customer->getFirstname(). ' '. $customer->getLastname(),
                'email' => $customer->getEmail(),
            );

            $transport = $this->transporter
                ->setTemplateIdentifier($this->emailTemplateConfig->getCompanyCreateNotifyAdminTemplateId())
                ->setTemplateOptions(['area' => \Magento\Framework\App\Area::AREA_FRONTEND, 'store' => $storeId])
                ->setTemplateVars($templateParams)
                ->setFrom($sender)
                ->addTo($toEmail)
                ->setReplyTo($customer->getEmail())
                ->addBcc(($copyTo && $copyMethod == 'bcc') ? explode(',', $copyTo) : [])
                ->getTransport();
            try {
                $transport->sendMessage();
            } catch (MailException $e) {

            }
        }

        return $this;
    }

    /**
     * Send corresponding email template.
     *
     * @param string $customerEmail
     * @param string $customerName
     * @param string $templateId
     * @param string|array $sender configuration path of email identity
     * @param array $templateParams [optional]
     * @param int|null $storeId [optional]
     * @param array $bcc [optional]
     * @return void
     */
    private function sendEmailTemplate(
        $customerEmail,
        $customerName,
        $templateId,
        $sender,
        array $templateParams = [],
        $storeId = null,
        array $bcc = []
    ) {
        $from = $sender;

        $this->transporter->sendMessage(
            $customerEmail,
            $customerName,
            $from,
            $templateId,
            $templateParams,
            $storeId,
            $bcc
        );
    }

    /**
     * Get either first store ID from a set website or the provided as default.
     *
     * @param CustomerInterface $customer
     * @return int
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function getWebsiteStoreId(CustomerInterface $customer)
    {
        $defaultStoreId = \Magento\Store\Model\Store::DEFAULT_STORE_ID;
        if ($customer->getWebsiteId() != 0) {
            $storeIds = $this->storeManager->getWebsite($customer->getWebsiteId())->getStoreIds();
            reset($storeIds);
            $defaultStoreId = current($storeIds);
        }
        return $defaultStoreId;
    }
}