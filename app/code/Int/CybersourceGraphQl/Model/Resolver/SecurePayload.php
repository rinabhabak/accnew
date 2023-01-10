<?php

namespace Int\CybersourceGraphQl\Model\Resolver;

use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;

class SecurePayload implements ResolverInterface
{
    /**
     * @var \ParadoxLabs\CyberSource\Model\Service\SecureAcceptance\FrontendRequest
     */
    protected $secureAcceptRequest;

    protected $request;

    protected $customerFactory;

    protected $storeManager;

    public function __construct(
        \ParadoxLabs\CyberSource\Model\Service\SecureAcceptance\FrontendRequest $secureAcceptRequest,
        \Magento\Customer\Model\CustomerFactory $customerFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\App\RequestInterface $request
    ){
        $this->secureAcceptRequest = $secureAcceptRequest;
        $this->customerFactory = $customerFactory;
        $this->storeManager = $storeManager;
        $this->request = $request;
    }
    public function resolve(
        Field $field,
              $context,
        ResolveInfo $info,
        array $value = null,
        array $args = null
    ) {
        $cardData = $args['input'];
        $this->request->setParam('guest_email', $cardData['guest_email']);
        $this->request->setParam('source', $cardData['guest_email']);
        $this->request->setPostValue('billing', $cardData['billing']);

        $iframeUrl = $this->secureAcceptRequest->getIframeUrl();
        $iframeParams = $this->secureAcceptRequest->getIframeParams();

        $websiteID = $this->storeManager->getStore()->getWebsiteId();
        $customer = $this->customerFactory->create()->setWebsiteId($websiteID)->loadByEmail($cardData['guest_email']);

        if($customer && $customer->getId()){
            $iframeParams['consumer_id'] = $customer->getId();
        }

        return [
            'iframeAction' => $iframeUrl,
            'iframeParams' => [$iframeParams],
        ];
    }
}