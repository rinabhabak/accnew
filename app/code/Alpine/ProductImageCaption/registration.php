<?php
/**
 * All the images should also show models number as well. The same issues happens in the cart and view and edit cart and check out.
 *
 * @category    Alpine
 * @package     Alpine_ProductImageCaption
 * @copyright   Copyright (c) 2018 Alpine Consulting, Inc
 * @author      Dmitry Naumov <dmitry.naumov@alpineinc.com>
 */
\Magento\Framework\Component\ComponentRegistrar::register(
    \Magento\Framework\Component\ComponentRegistrar::MODULE,
    'Alpine_ProductImageCaption',
    __DIR__
);
