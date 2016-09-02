<?php
declare(strict_types=1);
namespace Viserio\Parsers\Providers;

use Interop\Container\ContainerInterface;
use Interop\Container\ServiceProvider;
use Viserio\Parsers\FileLoader;
use Viserio\Contracts\Parsers\Loader as LoaderContract;
use Viserio\Parsers\TaggableParser;

class ParsersServiceProvider implements ServiceProvider
{
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
        return new FileLoader($container->get(TaggableParser::class));
    }

    public static function createTaggableParser(): TaggableParser
    {
        return new TaggableParser();
    }
}
