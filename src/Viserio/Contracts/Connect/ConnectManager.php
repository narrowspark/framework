<?php
namespace Viserio\Contracts\Connect;

interface ConnectManager
{
     /**
     * All supported PDO drivers.
     *
     * @return string[]
     */
    public function supportedPDODrivers(): array;

    /**
     * Get all available drivers on system.
     *
     * @return array
     */
    public function getAvailableDrivers(): array;
}
