<?php
/**
 * Copyright © Indusnet Technologies All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Int\RewriteMagentoCore\Rewrite\Magento\Theme\Controller\Result;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Framework\App\Response\Http;

class JsFooterPlugin extends \Magento\Theme\Controller\Result\JsFooterPlugin
{
    private const XML_PATH_DEV_MOVE_JS_TO_BOTTOM = 'dev/js/move_script_to_bottom';

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig
    ){
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * Put all javascript to footer before sending the response.
     *
     * @param Http $subject
     * @return void
     */
    public function beforeSendResponse(Http $subject)
    {
        $content = $subject->getContent();
        $script = [];
        if(!empty($content)){
            if (is_string($content) && strpos($content, '</body') !== false) {
                if ($this->scopeConfig->isSetFlag(
                    self::XML_PATH_DEV_MOVE_JS_TO_BOTTOM,
                    ScopeInterface::SCOPE_STORE
                )
                ) {
                    $pattern = '#<script[^>]*+(?<!text/x-magento-template.)>.*?</script>#is';
                    $content = preg_replace_callback(
                        $pattern,
                        function ($matchPart) use (&$script) {
                            $script[] = $matchPart[0];
                            return '';
                        },
                        $content
                    );
                    $subject->setContent(
                        str_replace('</body', implode("\n", $script) . "\n</body", $content)
                    );
                }
            }
        }
    }
}

