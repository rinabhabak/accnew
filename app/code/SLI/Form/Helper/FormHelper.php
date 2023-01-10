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

namespace SLI\Form\Helper;

use Magento\Framework\App\Helper\Context;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\Module\ResourceInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Store\Model\ScopeInterface;

/**
 * Search form helper
 */
class FormHelper extends AbstractHelper
{
    const XML_PATH_ENABLED = 'sli_search_form/general/enabled';

    const XML_PATH_CUSTOM_FORM_CODE = 'sli_search_form/search_form_code/custom_form_code';

    const XML_PATH_HEADER = 'sli_search_form/javascript/header';

    const XML_PATH_FOOTER = 'sli_search_form/javascript/footer';

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var ResourceInterface
     */
    protected $moduleResource;

    /**
     * @param Context $context
     * @param StoreManagerInterface $storeManager
     * @param ResourceInterface $moduleResource
     */
    public function __construct(Context $context, StoreManagerInterface $storeManager, ResourceInterface $moduleResource)
    {
        parent::__construct($context);
        $this->storeManager = $storeManager;
        $this->moduleResource = $moduleResource;
    }

    /**
     * Get current version.
     *
     * @return string
     */
    public function getVersion()
    {
        return $this->moduleResource->getDbVersion('SLI_Form');
    }

    /**
     * Check if search form allowed
     *
     * @param int $storeId
     *
     * @return bool
     */
    public function isAllowed($storeId)
    {
        return 1 == (int)$this->scopeConfig->getValue(
            FormHelper::XML_PATH_ENABLED,
            ScopeInterface::SCOPE_STORE,
            $storeId);
    }

    /**
     * Retrieve the custom code from the database
     *
     * @param string $path
     *
     * @return string
     */
    public function getCustomCode($path)
    {
        $storeId = $this->storeManager->getStore()->getStoreId();
        if (!$this->isAllowed($storeId)) {
            return '';
        }

        return $this->scopeConfig->getValue($path, ScopeInterface::SCOPE_STORE, $storeId);
    }
}
