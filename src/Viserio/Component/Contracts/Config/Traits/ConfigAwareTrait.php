<?php
declare(strict_types=1);
namespace Viserio\Component\Contracts\Config\Traits;

use RuntimeException;
use Viserio\Component\Contracts\Config\Repository as RepositoryContract;

trait ConfigAwareTrait
{
    /**
     * Config instance.
     *
     * @var \Viserio\Component\Contracts\Config\Repository|null
     */
    protected $config;

    /**
     * Set a Config.
     *
     * @param \Viserio\Component\Contracts\Config\Repository $config
     *
     * @return $this
     */
    public function setConfig(RepositoryContract $config)
    {
        $this->config = $config;

        return $this;
    }

    /**
     * Get the Config.
     *
     * @throws \RuntimeException
     *
     * @return \Viserio\Component\Contracts\Config\Repository
     */
    public function getConfig(): RepositoryContract
    {
        if (! $this->config) {
            throw new RuntimeException('Config is not set up.');
        }

        return $this->config;
    }
}
