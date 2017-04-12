<?php
declare(strict_types=1);
namespace Viserio\Component\Parsers\Providers;

use Interop\Container\ContainerInterface;
use Interop\Container\ServiceProvider;
use Viserio\Component\Contracts\OptionsResolver\ProvidesDefaultOptions as ProvidesDefaultOptionsContract;
use Viserio\Component\Contracts\OptionsResolver\RequiresComponentConfig as RequiresComponentConfigContract;
use Viserio\Component\Contracts\Parsers\Loader as LoaderContract;
use Viserio\Component\Contracts\Parsers\TaggableParser as TaggableParserContract;
use Viserio\Component\OptionsResolver\OptionsResolver;
use Viserio\Component\Parsers\FileLoader;
use Viserio\Component\Parsers\TaggableParser;

class ParsersServiceProvider implements ServiceProvider
{
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

    /**
     * Create a file loader.
     *
     * @return \Viserio\Component\Contracts\Parsers\Loader
     */
    public static function createFileLoader(ContainerInterface $container): LoaderContract
    {
        return new FileLoader($container->get(TaggableParser::class));
    }

    /**
     * Create a taggable parser.
     *
     * @return \Viserio\Component\Contracts\Parsers\TaggableParser
     */
    public static function createTaggableParser(): TaggableParserContract
    {
        return new TaggableParser();
    }
}
