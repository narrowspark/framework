<?php
declare(strict_types=1);
namespace Viserio\Component\Config\Providers;

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

    public static function createRepository($container): RepositoryContract
    {
        $config = new Repository();

        if ($container->has(LoaderContract::class)) {
            $config->setLoader($container->get(LoaderContract::class));
        }

        return $config;
    }
}
