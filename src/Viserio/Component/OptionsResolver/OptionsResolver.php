<?php
declare(strict_types=1);
namespace Viserio\Component\OptionsResolver;

use Viserio\Component\Contracts\OptionsResolver\Resolver as ResolverContract;
use Interop\Container\ContainerInterface;
use Viserio\Component\Contracts\Config\Repository as RepositoryContract;

class OptionsResolver implements ResolverContract
{
    /**
     * Create a new file view loader instance.
     *
     * @param \Interop\Container\ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->configureOptions($container);
    }

    /**
     * Create configuration.
     *
     * @param \Interop\Container\ContainerInterface $container
     *
     * @throws \RuntimeException
     *
     * @return array|\ArrayAccess
     */
    private function configureOptions(ContainerInterface $container)
    {
        if ($container->has(RepositoryContract::class)) {
            return $container->get(RepositoryContract::class);
        } elseif ($container->has('config')) {
            return $container->get('config');
        } elseif ($container->has('options')) {
            return $container->get('options');
        } else {
            throw new RuntimeException('No configuration found.');
        }
    }
}
