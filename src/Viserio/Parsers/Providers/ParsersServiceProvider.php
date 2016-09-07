<?php
declare(strict_types=1);
namespace Viserio\Parsers\Providers;

use Interop\Container\ContainerInterface;
use Interop\Container\ServiceProvider;
use Viserio\Config\Manager as ConfigManager;
use Viserio\Contracts\Parsers\Loader as LoaderContract;
use Viserio\Parsers\FileLoader;
use Viserio\Parsers\TaggableParser;

class ParsersServiceProvider implements ServiceProvider
{
    const PACKAGE = 'viserio.parsers';

    /**
     * {@inheritdoc}
     */
    public function getServices()
    {
        return [
            FileLoader::class => [self::class, 'createFileLoader'],
            LoaderContract::class => function (ContainerInterface $container) {
                return $container->get(FileLoader::class);
            },
            'parser' => function (ContainerInterface $container) {
                return $container->get(FileLoader::class);
            },
            TaggableParser::class => [self::class, 'createTaggableParser'],
        ];
    }

    public static function createFileLoader(ContainerInterface $container): FileLoader
    {
        if ($container->has(ConfigManager::class)) {
            $config = $container->get(ConfigManager::class)->get('parsers', []);
        } else {
            $config = self::get($container, 'options', []);
        }

        return new FileLoader(
            $container->get(TaggableParser::class),
            $config['directories'] ?? []
        );
    }

    public static function createTaggableParser(): TaggableParser
    {
        return new TaggableParser();
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
