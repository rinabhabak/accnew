<?php
/**
 * Indusnet Technologies.
 *
 * @category  Indusnet
 * @package   Int_CustomerGraphQl
 * @author    Indusnet
 */

namespace Int\CustomerGraphQl\Helper;
use Magento\Company\Model\Company;


/**
 * Class Data
 * @package Int\CustomerGraphQl\Model\Resolver
 */
class Data 
{

     /**
     * Set status label
     *
     * @param int $key
     * @return string
     */
    public function setStatusLabel($key)
    {
        $labels = [
            Company::STATUS_PENDING => __('Pending Approval'),
            Company::STATUS_APPROVED => __('Active'),
            Company::STATUS_REJECTED => __('Rejected'),
            Company::STATUS_BLOCKED => __('Blocked')
        ];

        return $labels[$key];
    }
}