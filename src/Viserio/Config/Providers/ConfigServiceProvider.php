<?php
declare(strict_types=1);
namespace Viserio\Config\Providers;

use Interop\Container\ContainerInterface;
use Interop\Container\ServiceProvider;
use Viserio\Config\Repository;
use Viserio\Contracts\Config\Repository as RepositoryContract;

class ConfigServiceProvider implements ServiceProvider
{
    /**
     * {@inheritdoc}
     */
    public function getServices()
    {
        return [
            RepositoryContract::class => [self::class, 'createRepository'],
            Repository::class => function (ContainerInterface $container) {
                return $container->get(RepositoryContract::class);
            },
            'config' => function (ContainerInterface $container) {
                return $container->get(RepositoryContract::class);
            },
        ];
    }

    public static function createRepository(): RepositoryContract
    {
        return new Repository();
    }
}
