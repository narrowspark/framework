<?php
namespace Viserio\Mail;

use Viserio\Support\AbstractManager;

class TransportManager extends AbstractManager
{
    /**
     * Set the default cache driver name.
     *
     * @param string $name
     */
    public function setDefaultDriver(string $name)
    {
    }

    /**
     * Get the default driver name.
     *
     * @return string
     */
    public function getDefaultDriver(): string
    {
    }

    /**
     * Get the configuration name.
     *
     * @return string
     */
    protected function getConfigName(): string
    {
        return 'mail';
    }
}
