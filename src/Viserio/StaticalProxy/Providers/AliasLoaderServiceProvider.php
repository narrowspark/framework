<?php
declare(strict_types=1);
namespace Viserio\StaticalProxy\Providers;

use Interop\Container\ContainerInterface;
use Interop\Container\ServiceProvider;
use Viserio\StaticalProxy\AliasLoader;
use Viserio\Config\Manager as ConfigManager;

class AliasLoaderServiceProvider implements ServiceProvider
{
    const PACKAGE = 'viserio.staticalproxy';

    /**
     * {@inheritdoc}
     */
    public function getServices()
    {
        return [
            AliasLoader::class => [self::class, 'createAliasLoader'],
            'alias' => function (ContainerInterface $container) {
                return $container->get(AliasLoader::class);
            },
        ];
    }

    public static function createAliasLoader(ContainerInterface $container): AliasLoader
    {
        return new AliasLoader(self::get($container, 'aliases', []));
    }

    /**
     * Returns the entry named PACKAGE.$name, of simply $name if PACKAGE.$name is not found.
     *
     * @param ContainerInterface $container
     * @param string             $name
     *
     * @return mixed
     */
    private static function get(ContainerInterface $container, string $name, $default = null)
    {
        $namespacedName = self::PACKAGE . '.' . $name;

        return $container->has($namespacedName) ? $container->get($namespacedName) :
            ($container->has($name) ? $container->get($name) : $default);
    }
}
