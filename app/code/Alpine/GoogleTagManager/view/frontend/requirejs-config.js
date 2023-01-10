/**
 * Alpine_GoogleTagManager
 *
 * @category    Alpine
 * @package     Alpine_GoogleTagManager
 * @copyright   Copyright (c) 2019 Alpine Consulting, Inc (www.alpineinc.com)
 * @author      Evgeniy Derevyanko <evgeniy.derevyanko@alpineinc.com>
 */

var config = {
    config: {
        mixins: {
            'Magento_GoogleTagManager/js/google-tag-manager-cart': {
                'Alpine_GoogleTagManager/js/cart-manager': true
            }
        }
    },
    map: {
        '*': {
            'Magento_GoogleTagManager/js/google-analytics-universal':'Alpine_GoogleTagManager/js/analytics-universal'
        }
    }
};