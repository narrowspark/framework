<?php
declare(strict_types=1);
namespace Viserio\Component\Support\Traits;

use Interop\Container\ContainerInterface;
use RuntimeException;
use Viserio\Component\Contracts\Config\Repository as RepositoryContract;

trait CreateOptionsTrait
{
    /**
     * Config array.
     *
     * @var array|\ArrayAccess
     */
    protected $options = [];

    /**
     * Create configuration.
     *
     * @param \Interop\Container\ContainerInterface $container
     *
     * @throws \RuntimeException
     *
     * @return void
     */
    protected function configureOptions(ContainerInterface $container): void
    {
        if ($this->$options === null) {
            if ($container->has(RepositoryContract::class)) {
                $options = $container->get(RepositoryContract::class);
            } elseif ($container->has('config')) {
                $options = $container->get('config');
            } elseif ($container->has('options')) {
                $options = $container->get('options');
            } else {
                throw new RuntimeException('No configuration found.');
            }

            $this->$options = $this->options($options);
        }
    }
}
