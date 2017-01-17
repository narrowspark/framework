<?php
declare(strict_types=1);
namespace Viserio\Component\Parsers\Providers;

use Interop\Container\ContainerInterface;
use Interop\Container\ServiceProvider;
use Viserio\Component\Contracts\Parsers\Loader as LoaderContract;
use Viserio\Component\Contracts\Parsers\TaggableParser as TaggableParserContract;
use Viserio\Component\Contracts\Support\Traits\ServiceProviderConfigAwareTrait;
use Viserio\Component\Parsers\FileLoader;
use Viserio\Component\Parsers\TaggableParser;

class ParsersServiceProvider implements ServiceProvider
{
    use ServiceProviderConfigAwareTrait;

    public const PACKAGE = 'viserio.parsers';

    /**
     * {@inheritdoc}
     */
    public function getServices()
    {
        return [
            LoaderContract::class => [self::class, 'createFileLoader'],
            FileLoader::class     => function (ContainerInterface $container) {
                return $container->get(LoaderContract::class);
            },
            TaggableParserContract::class => [self::class, 'createTaggableParser'],
            TaggableParser::class         => function (ContainerInterface $container) {
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
