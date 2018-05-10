<?php
declare(strict_types=1);
namespace Viserio\Component\Config\Provider;

use Interop\Container\ServiceProviderInterface;
use Psr\Container\ContainerInterface;
use Viserio\Component\Config\Repository;
use Viserio\Component\Contract\Config\Repository as RepositoryContract;
use Viserio\Component\Contract\Parser\Loader as LoaderContract;

class ConfigServiceProvider implements ServiceProviderInterface
{
    /**
     * {@inheritdoc}
     */
    public function getFactories(): array
    {
        return [
            RepositoryContract::class => [self::class, 'createRepository'],
            Repository::class         => function (ContainerInterface $container) {
                return $container->get(RepositoryContract::class);
            },
            'config' => function (ContainerInterface $container) {
                return $container->get(RepositoryContract::class);
            },
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getExtensions(): array
    {
        return [];
    }

    /**
     * Create a new Repository instance.
     *
     * @param \Psr\Container\ContainerInterface $container
     *
     * @return \Viserio\Component\Contract\Config\Repository
     */
    public static function createRepository(ContainerInterface $container): RepositoryContract
    {
        $config = new Repository();

        if ($container->has(LoaderContract::class)) {
            $config->setLoader($container->get(LoaderContract::class));
        }

        return $config;
    }
}
