<?php

namespace Int\CybersourceGraphQl\Model\Resolver;

use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;

class CardinalAuth implements ResolverInterface
{

    /**
     * @var \ParadoxLabs\CyberSource\Model\Service\CardinalCruise\Persistor
     */
    protected $persistor;

    /**
     * @var \ParadoxLabs\CyberSource\Model\Service\CardinalCruise\JsonWebTokenGenerator
     */
    protected $jsonWebTokenGenerator;

    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    protected $request;

    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $checkoutSession;

    /**
     * @var \Magento\Quote\Api\CartRepositoryInterface
     */
    protected $quoteRepository;

    /**
     * @param \ParadoxLabs\CyberSource\Model\Service\CardinalCruise\Persistor $persistor
     * @param \ParadoxLabs\CyberSource\Model\Service\CardinalCruise\JsonWebTokenGenerator $jsonWebTokenGenerator
     * @param \Magento\Framework\App\RequestInterface $request
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \Magento\Quote\Api\CartRepositoryInterface $quoteRepository
     */
    public function __construct(
        \ParadoxLabs\CyberSource\Model\Service\CardinalCruise\Persistor $persistor,
        \ParadoxLabs\CyberSource\Model\Service\CardinalCruise\JsonWebTokenGenerator $jsonWebTokenGenerator,
        \Magento\Framework\App\RequestInterface $request,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Quote\Api\CartRepositoryInterface $quoteRepository
    )
    {
        $this->persistor = $persistor;
        $this->jsonWebTokenGenerator = $jsonWebTokenGenerator;
        $this->request = $request;
        $this->checkoutSession = $checkoutSession;
        $this->quoteRepository = $quoteRepository;
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

        try {
            $enrollReply = $this->persistor->loadPayerAuthEnrollReply();

            $payload = [
                'authPayload'  => $this->getAuthPayload($enrollReply),
                'orderPayload' => $this->getOrderPayload($enrollReply),
                'JWT' => $this->jsonWebTokenGenerator->getJwt(),
            ];

            return $payload;
        } catch (\Exception $exception) {

        }
        return [];
    }

    /**
     * @param array $enrollReply
     * @return array
     */
    protected function getAuthPayload(array $enrollReply)
    {
        return [
            'AcsUrl' => $enrollReply['acsURL'],
            'Payload' => $enrollReply['paReq'],
        ];
    }

    /**
     * @param array $enrollReply
     * @return array
     */
    protected function getOrderPayload(array $enrollReply)
    {
        /** @var \Magento\Quote\Model\Quote $quote */
        $quote = $this->checkoutSession->getQuote();

        if (empty($quote->getReservedOrderId())) {
            $quote->reserveOrderId();
            $this->quoteRepository->save($quote);
        }

        return [
            'OrderDetails' => [
                'TransactionId' => $enrollReply['authenticationTransactionID'],
                'OrderNumber' => $quote->getReservedOrderId(),
            ],
        ];
    }


}