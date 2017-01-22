<?php
declare(strict_types=1);
namespace Viserio\Component\Contracts\Support\Traits;

use Interop\Container\ContainerInterface;
use Viserio\Component\Contracts\Config\Repository as RepositoryContract;

trait CreateConfigurationTrait
{
    /**
     * Handler config.
     *
     * @var array|\ArrayAccess
     */
    protected $config = [];

    /**
     * Create handler configuration.
     *
     * @param \Interop\Container\ContainerInterface $container
     *
     * @return void
     */
    protected function createConfiguration(ContainerInterface $container): void
    {
        if ($container->has(RepositoryContract::class)) {
            $config = $container->get(RepositoryContract::class);
        } else {
            $config = $container->get('config');
        }

        $this->config = $this->options($config);
    }
}
