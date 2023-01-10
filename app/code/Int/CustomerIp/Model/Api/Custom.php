<?php
namespace Int\CustomerIp\Model\Api;

use Psr\Log\LoggerInterface;
use Magento\Framework\HTTP\PhpEnvironment\RemoteAddress;

class Custom
{
    protected $logger;
    protected $remoteAddress;

    /**
     * @var \Magento\Framework\HTTP\PhpEnvironment\Request
     */
    protected $_httpRequest;

    public function __construct(
        LoggerInterface $logger,
        RemoteAddress $remoteAddress,
        \Magento\Framework\HTTP\PhpEnvironment\Request $httpRequest
    )
    {
        $this->logger = $logger;
        $this->remoteAddress = $remoteAddress;
        $this->_httpRequest = $httpRequest;
    }

    /**
     * @inheritdoc
     */

    public function getCustomerApiAddress()
    {
        $response = ['success' => false];

        try {
        $response = ['success' => true, 'ip' => /* $this->_httpRequest->getClientIp() */ $this->remoteAddress->getRemoteAddress()  ];
        } catch (\Exception $e) {
            $response = ['success' => false, 'message' => $e->getMessage()];
            $this->logger->info($e->getMessage());
        }

        $returnArray[] = $response;
        return $returnArray;
    }
}