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

namespace SLI\Api\Helper;

use Magento\Customer\Model\Session;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Data\Form\FormKey;

/**
 * Obtains data related to the Cart endpoint.
 */
class CartData extends AbstractHelper
{
    /**
     * Users Form Key
     * @var formKey
     */
    protected $formKey;

    /**
     * @param Context $context
     * @param FormKey $formKey
     */
    public function __construct(Context $context, FormKey $formKey)
    {
        parent::__construct($context);
        $this->formKey = $formKey;
    }

    /**
     * Get the information required for rendering the API response.
     *  - username
     *  - formkey
     *
     * @param Session $session the user session
     *
     * @return array containing the information
     */
    public function getInfo($session)
    {
        $result = [];

        $result['form_key'] = $this->formKey->getFormKey();
        $result['user'] = $this->getUserSessionInfo($session);

        return $result;
    }

    /**
     * Get the user's name and login state.
     *
     * @param $session Session
     *
     * @return array
     */
    protected function getUserSessionInfo($session)
    {
        $result = [];

        $result['user_name'] = $session->isLoggedIn() ? $session->getCustomer()->getName() : ''; // TODO Fix deprecated call
        $result['logged_in'] = $session->isLoggedIn();

        return $result;
    }
}
