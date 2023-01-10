<?php
/**
 * Copyright (c) 2015 S.L.I. Systems, Inc. (www.sli-systems.com) - All Rights Reserved
 * This file is part of Learning Search Connect.
 * Learning Search Connect is distributed under a limited and restricted
 * license – please visit www.sli-systems.com/LSC for full license details.
 *
 * THIS CODE AND INFORMATION ARE PROVIDED "AS IS" WITHOUT WARRANTY OF ANY
 * KIND, EITHER EXPRESSED OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE
 * IMPLIED WARRANTIES OF MERCHANTABILITY AND/OR FITNESS FOR A
 * PARTICULAR PURPOSE. TO THE MAXIMUM EXTENT PERMITTED BY APPLICABLE LAW, IN NO
 * EVENT WILL SLI BE LIABLE TO YOU OR ANY OTHER PARTY FOR ANY GENERAL, DIRECT,
 * INDIRECT, SPECIAL, INCIDENTAL OR CONSEQUENTIAL LOSS OR DAMAGES OF ANY
 * CHARACTER ARISING OUT OF THE USE OF THE CODE AND/OR THE LICENSE INCLUDING
 * BUT NOT LIMITED TO PERSONAL INJURY, LOSS OF DATA, LOSS OF PROFITS, LOSS OF
 * ASSIGNMENTS, DATA OR OUTPUT FROM THE SERVICE BEING RENDERED INACCURATE,
 * FAILURE OF CODE, SERVER DOWN TIME, DAMAGES FOR LOSS OF GOODWILL, BUSINESS
 * INTERRUPTION, COMPUTER FAILURE OR MALFUNCTION, OR ANY AND ALL OTHER DAMAGES
 * OR LOSSES OF WHATEVER NATURE, EVEN IF SLI HAS BEEN INFORMED OF THE
 * POSSIBILITY OF SUCH DAMAGES.
 */

namespace SLI\Feed\Controller\Adminhtml\System\Config\Feed;

use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use SLI\Feed\Model\GenerateFlag;
use SLI\Feed\Controller\Adminhtml\System\Config\Feed;

class Status extends Feed
{
    /**
     * @var JsonFactory
     */
    protected $resultJsonFactory;

    /**
     * @param Context $context
     * @param JsonFactory $resultJsonFactory
     */
    public function __construct(Context $context, JsonFactory $resultJsonFactory)
    {
        parent::__construct($context);
        $this->resultJsonFactory = $resultJsonFactory;
    }

    /**
     * Retrieve generate process state and it's parameters in json format
     *
     * @return \Magento\Framework\Controller\Result\Json
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function execute()
    {
        $result = [];
        $generateFlag = $this->getGenerateFlag();

        if ($generateFlag) {
            $state = $generateFlag->getState();

            switch ($state) {
                case GenerateFlag::STATE_INACTIVE:
                    $state = GenerateFlag::STATE_INACTIVE;
                    break;
                case GenerateFlag::STATE_RUNNING:
                    if (!$generateFlag->isTimeout()) {
                        break;
                    }

                    $flagData = $generateFlag->getFlagData();
                    if (is_array($flagData) && !(isset($flagData['timeout_reached']) && $flagData['timeout_reached'])) {
                        $message = sprintf('The timeout limit (%s hours) for response from feed generation was reached.',
                            floor(GenerateFlag::FLAG_TTL / 3600));
                        $this->_objectManager->get(
                            'Psr\Log\LoggerInterface'
                        )->critical(
                            new \Magento\Framework\Exception\LocalizedException(__($message))
                        );
                        $state = GenerateFlag::STATE_FINISHED;
                        $flagData['has_errors'] = true;
                        $flagData['timeout_reached'] = true;
                        $flagData['message'] = $message;
                        $generateFlag->setState($state)->setFlagData($flagData)->save();
                    }
                    // fall-through intentional
                case GenerateFlag::STATE_FINISHED:
                case GenerateFlag::STATE_NOTIFIED:
                    $flagData = $generateFlag->getFlagData();
                    if (!isset($flagData['has_errors'])) {
                        $flagData['has_errors'] = false;
                    }
                    $result['has_errors'] = $flagData['has_errors'];
                    if (!isset($flagData['message'])) {
                        $flagData['message'] = '';
                    }

                    if ($result['has_errors']) {
                        $result['message'] = sprintf('<div class="message message-error">%s</div>', $flagData['message']);
                    } else {
                        $result['message'] = sprintf('<div class="message message-notice">%s</div>', $flagData['message']);
                    }

                    if (isset($flagData['exception'])) {
                        $result['exception'] = $flagData['exception'];
                    }

                    break;
                default:
                    $state = GenerateFlag::STATE_INACTIVE;
                    break;
            }
        } else {
            $state = GenerateFlag::STATE_INACTIVE;
        }
        $result['state'] = $state;
        /** @var \Magento\Framework\Controller\Result\Json $resultJson */
        $resultJson = $this->resultJsonFactory->create();

        return $resultJson->setData($result);
    }
}
