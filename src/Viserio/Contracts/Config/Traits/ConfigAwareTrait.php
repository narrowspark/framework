<?php
declare(strict_types=1);
namespace Viserio\Contracts\Config\Traits;

use RuntimeException;
use Viserio\Contracts\Config\Manager;

trait ConfigAwareTrait
{
    /**
     * Config instance.
     *
     * @var \Viserio\Contracts\Config\Manager|null
     */
    protected $config;

    /**
     * Set a Config.
     *
     * @param \Viserio\Contracts\Config\Manager $config
     *
     * @return $this
     */
    public function setConfig(Manager $config)
    {
        $this->config = $config;

        return $this;
    }

    /**
     * Get the Config.
     *
     * @throws \RuntimeException
     *
     * @return \Viserio\Contracts\Config\Manager
     */
    public function getConfig(): Manager
    {
        if (! $this->config) {
            throw new RuntimeException('Config is not set up.');
        }

        return $this->config;
    }
}
