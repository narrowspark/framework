<?php
declare(strict_types=1);
namespace Viserio\Parsers\Providers;

use Interop\Container\ContainerInterface;
use Interop\Container\ServiceProvider;
use Viserio\Config\Manager as ConfigManager;
use Viserio\Contracts\Parsers\Loader as LoaderContract;
use Viserio\Contracts\Parsers\TaggableParser as TaggableParserContract;
use Viserio\Contracts\Support\Traits\ServiceProviderConfigAwareTrait;
use Viserio\Parsers\FileLoader;
use Viserio\Parsers\TaggableParser;

class ParsersServiceProvider implements ServiceProvider
{
    use ServiceProviderConfigAwareTrait;

    const PACKAGE = 'viserio.parsers';

    /**
     * {@inheritdoc}
     */
    public function getServices()
    {
        return [
            LoaderContract::class => [self::class, 'createFileLoader'],
            FileLoader::class => function (ContainerInterface $container) {
                return $container->get(LoaderContract::class);
            },
            TaggableParserContract::class => [self::class, 'createTaggableParser'],
            TaggableParser::class => function (ContainerInterface $container) {
                return $container->get(TaggableParserContract::class);
            },
            'parser' => function (ContainerInterface $container) {
                return $container->get(TaggableParserContract::class);
            },
        ];
    }

    public static function createFileLoader(ContainerInterface $container): FileLoader
    {
        return new FileLoader(
            $container->get(TaggableParser::class),
            self::getConfig($container, 'directories', [])
        );
    }

    public static function createTaggableParser(): TaggableParser
    {
        return new TaggableParser();
    }
}
