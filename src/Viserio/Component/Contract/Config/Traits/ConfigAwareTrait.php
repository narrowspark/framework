<?php
declare(strict_types=1);
namespace Viserio\Component\Contract\Config\Traits;

use RuntimeException;
use Viserio\Component\Contract\Config\Repository as RepositoryContract;

trait ConfigAwareTrait
{
    /**
     * Config instance.
     *
     * @var null|\Viserio\Component\Contract\Config\Repository
     */
    protected $config;

    /**
     * Set a Config.
     *
     * @param \Viserio\Component\Contract\Config\Repository $config
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
     * @return \Viserio\Component\Contract\Config\Repository
     */
    public function getConfig(): RepositoryContract
    {
        if (! $this->config) {
            throw new RuntimeException('Config is not set up.');
        }

        return $this->config;
    }
}
