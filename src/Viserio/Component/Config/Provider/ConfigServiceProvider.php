<?php
declare(strict_types=1);
namespace Viserio\Component\Config\Provider;

use Interop\Container\ServiceProvider;
use Psr\Container\ContainerInterface;
use Viserio\Component\Config\Repository;
use Viserio\Component\Contracts\Config\Repository as RepositoryContract;
use Viserio\Component\Contracts\Parsers\Loader as LoaderContract;

class ConfigServiceProvider implements ServiceProvider
{
    /**
     * {@inheritdoc}
     */
    public function getServices()
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
     * Create a new Repository instance.
     *
     * @param \Psr\Container\ContainerInterface $container
     *
     * @return \Viserio\Component\Contracts\Config\Repository
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
