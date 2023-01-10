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

namespace SLI\Form\Block;

use Magento\Framework\View\Element\Template;
use Magento\Backend\Block\Template\Context;
use SLI\Form\Helper\FormHelper;

/**
 * Header block
 */
class Header extends Template
{
    /**
     * Search form helper
     *
     * @var FormHelper
     */
    protected $formHelper = null;

    /**
     * @param Context $context
     * @param FormHelper $formHelper
     * @param array $data
     */
    public function __construct(Context $context, FormHelper $formHelper, array $data = [])
    {
        parent::__construct($context, $data);
        $this->formHelper = $formHelper;
    }

    /**
     * Add the custom header code
     *
     * @return string
     */
    protected function _toHtml() {
        $data = $this->formHelper->getCustomCode(FormHelper::XML_PATH_HEADER);
        if ($data) {
            return $data;
        }

        return parent::_toHtml();
    }
}
