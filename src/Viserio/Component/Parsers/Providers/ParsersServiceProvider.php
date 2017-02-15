<?php
declare(strict_types=1);
namespace Viserio\Component\Parsers\Providers;

use Interop\Container\ContainerInterface;
use Interop\Container\ServiceProvider;
use Viserio\Component\Contracts\Parsers\Loader as LoaderContract;
use Viserio\Component\Contracts\Parsers\TaggableParser as TaggableParserContract;
use Viserio\Component\Parsers\FileLoader;
use Viserio\Component\Parsers\TaggableParser;
use Viserio\Component\Contracts\OptionsResolver\ProvidesDefaultOptions as ProvidesDefaultOptionsContract;
use Viserio\Component\Contracts\OptionsResolver\RequiresComponentConfig as RequiresComponentConfigContract;
use Viserio\Component\OptionsResolver\OptionsResolver;

class ParsersServiceProvider implements
    ServiceProvider,
    RequiresComponentConfigContract,
    ProvidesDefaultOptionsContract
{
    /**
     * Resolved cached options.
     *
     * @var array
     */
    private static $options;

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
     * {@inheritdoc}
     */
    public function getDimensions(): iterable
    {
        return ['viserio', 'parsers'];
    }

    /**
     * {@inheritdoc}
     */
    public function getDefaultOptions(): iterable
    {
        return [
            'directories' => [],
        ];
    }

    public static function createFileLoader(ContainerInterface $container): FileLoader
    {
        self::resolveOptions($container);

        return new FileLoader(
            $container->get(TaggableParser::class),
            self::$options['directories']
        );
    }

    public static function createTaggableParser(): TaggableParser
    {
        return new TaggableParser();
    }

    /**
     * Resolve component options.
     *
     * @param \Interop\Container\ContainerInterface $container
     *
     * @return void
     */
    private static function resolveOptions(ContainerInterface $container): void
    {
        if (self::$options === null) {
            self::$options = $container->get(OptionsResolver::class)
                ->configure(new static(), $container)
                ->resolve();
        }
    }
}
