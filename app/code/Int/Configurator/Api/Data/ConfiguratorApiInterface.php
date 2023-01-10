<?php

declare(strict_types=1);

namespace Int\Configurator\Api\Data;

/**
 * Represents a store and properties
 *
 * @api
 */
interface ConfiguratorApiInterface
{

    /**
     * Get Configurator.
     *
     * @return string|null
     */
    public function getConfigurator();

    /**
     * Set Configurator.
     *
     * @param string $id
     *
     * @return \Webkul\Marketplace\Api\Data\ProductInterface
     */
    public function setConfigurator($id);
}