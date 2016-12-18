<?php
declare(strict_types=1);
namespace Viserio\StaticalProxy\Providers;

use Interop\Container\ContainerInterface;
use Interop\Container\ServiceProvider;
use Viserio\Contracts\StaticalProxy\AliasLoader as AliasLoaderContract;
use Viserio\Contracts\Support\Traits\ServiceProviderConfigAwareTrait;
use Viserio\StaticalProxy\AliasLoader;

class AliasLoaderServiceProvider implements ServiceProvider
{
    use ServiceProviderConfigAwareTrait;

    const PACKAGE = 'viserio.staticalproxy';

    /**
     * {@inheritdoc}
     */
    public function getServices()
    {
        return [
            AliasLoaderContract::class => [self::class, 'createAliasLoader'],
            AliasLoader::class         => function (ContainerInterface $container) {
                return $container->get(AliasLoaderContract::class);
            },
            'alias' => function (ContainerInterface $container) {
                return $container->get(AliasLoaderContract::class);
            },
        ];
    }

    public static function createAliasLoader(ContainerInterface $container): AliasLoader
    {
        return new AliasLoader(self::getConfig($container, 'aliases', []));
    }
}
