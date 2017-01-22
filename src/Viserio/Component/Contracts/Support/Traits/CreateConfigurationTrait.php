<?php
declare(strict_types=1);
namespace Viserio\Component\Contracts\Support\Traits;

use RuntimeException;
use Interop\Container\ContainerInterface;
use Viserio\Component\Contracts\Config\Repository as RepositoryContract;

trait CreateConfigurationTrait
{
    /**
     * Config array.
     *
     * @var array|\ArrayAccess
     */
    protected $config = [];

    /**
     * Create configuration.
     *
     * @param \Interop\Container\ContainerInterface $container
     *
     * @return void
     */
    protected function createConfiguration(ContainerInterface $container): void
    {
        if ($this->config !== null) {
            if ($container->has(RepositoryContract::class)) {
                $config = $container->get(RepositoryContract::class);
            } elseif($container->has('config')) {
                $config = $container->get('config');
            } elseif($container->has('options')) {
                $config = $container->get('options');
            } else {
                throw new RuntimeException('No configuration found.');
            }
        }

        $this->config = $this->options($config);
    }
}
