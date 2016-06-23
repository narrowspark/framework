<?php
namespace Viserio\Config\Traits;

use RuntimeException;
use Viserio\Contracts\Config\Manager as ConfigManagerContract;

trait ConfigAwareTrait
{
    /**
     * The config instance.
     *
     * @var \Viserio\Contracts\Config\Manager
     */
    protected $config;

    /**
     * Set a config instance.
     *
     * @param \Viserio\Contracts\Config\Manager $config
     *
     * @return self
     */
    public function setConfig(ConfigManagerContract $config): self
    {
        $this->config = $config;

        return $this;
    }

    /**
     * Get the config.
     *
     * @throws \RuntimeException
     *
     * @return \Viserio\Contracts\Config\Manager
     */
    public function getConfig(): ConfigManagerContract
    {
        if (! $this->config) {
            throw new RuntimeException('Config is not set up.');
        }

        return $this->config;
    }
}
