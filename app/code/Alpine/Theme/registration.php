<?php
/**
 * Alpine_Theme
 *
 * @category    Alpine
 * @package     Alpine_Theme
 * @copyright   Copyright (c) 2018 Alpine Consulting, Inc
 * @author      Valery Shishkin <valery.shishkin@alpineinc.com>
 */

use \Magento\Framework\Component\ComponentRegistrar;

ComponentRegistrar::register(
    ComponentRegistrar::MODULE,
    'Alpine_Theme',
    __DIR__
);