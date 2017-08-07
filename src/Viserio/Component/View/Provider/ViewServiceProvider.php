<?php
declare(strict_types=1);
namespace Viserio\Component\View\Provider;

use Interop\Container\ServiceProvider;
use Parsedown;
use ParsedownExtra;
use Psr\Container\ContainerInterface;
use Viserio\Component\Contracts\Filesystem\Filesystem as FilesystemContract;
use Viserio\Component\Contracts\View\Factory as FactoryContract;
use Viserio\Component\Contracts\View\Finder as FinderContract;
use Viserio\Component\View\Engine\EngineResolver;
use Viserio\Component\View\Engine\FileEngine;
use Viserio\Component\View\Engine\MarkdownEngine;
use Viserio\Component\View\Engine\PhpEngine;
use Viserio\Component\View\ViewFactory;
use Viserio\Component\View\ViewFinder;

class ViewServiceProvider implements ServiceProvider
{
    /**
     * {@inheritdoc}
     */
    public function getServices()
    {
        return [
            EngineResolver::class  => [self::class, 'createEngineResolver'],
            'view.engine.resolver' => function (ContainerInterface $container) {
                return $container->get(EngineResolver::class);
            },
            FinderContract::class  => [self::class, 'createViewFinder'],
            ViewFinder::class      => function (ContainerInterface $container) {
                return $container->get(FinderContract::class);
            },
            'view.finder'          => function (ContainerInterface $container) {
                return $container->get(FinderContract::class);
            },
            FactoryContract::class      => [self::class, 'createViewFactory'],
            ViewFactory::class          => function (ContainerInterface $container) {
                return $container->get(FactoryContract::class);
            },
            'view'                  => function (ContainerInterface $container) {
                return $container->get(FactoryContract::class);
            },
        ];
    }

    public static function createEngineResolver(ContainerInterface $container): EngineResolver
    {
        $engines = new EngineResolver();

        // Next we will register the various engines with the engines so that the
        // environment can resolve the engines it needs for various views based
        // on the extension of view files. We call a method for each engines.
        foreach (['file', 'php', 'markdown'] as $engineClass) {
            self::{'register' . \ucfirst($engineClass) . 'Engine'}($engines, $container);
        }

        return $engines;
    }

    public static function createViewFinder(ContainerInterface $container): ViewFinder
    {
        return new ViewFinder($container->get(FilesystemContract::class), $container);
    }

    public static function createViewFactory(ContainerInterface $container): FactoryContract
    {
        $view = new ViewFactory(
            $container->get(EngineResolver::class),
            $container->get(ViewFinder::class)
        );

        $view->share('app', $container);

        return $view;
    }

    /**
     * Register the PHP engine implementation.
     *
     * @param \Viserio\Component\View\Engine\EngineResolver $engines
     * @param \Psr\Container\ContainerInterface             $container
     *
     * @return void
     */
    protected static function registerPhpEngine(EngineResolver $engines, ContainerInterface $container): void
    {
        $engines->register('php', function () {
            return new PhpEngine();
        });
    }

    /**
     * Register the File engine implementation.
     *
     * @param \Viserio\Component\View\Engine\EngineResolver $engines
     * @param \Psr\Container\ContainerInterface             $container
     *
     * @return void
     */
    protected static function registerFileEngine(EngineResolver $engines, ContainerInterface $container): void
    {
        $engines->register('file', function () {
            return new FileEngine();
        });
    }

    /**
     * Register the Markdown engine implementation.
     *
     * @param \Viserio\Component\View\Engine\EngineResolver $engines
     * @param \Psr\Container\ContainerInterface             $container
     *
     * @return void
     */
    protected static function registerMarkdownEngine(EngineResolver $engines, ContainerInterface $container): void
    {
        $markdown = null;

        if ($container->has(ParsedownExtra::class)) {
            $markdown = $container->get(ParsedownExtra::class);
        } elseif ($container->has(Parsedown::class)) {
            $markdown = $container->get(Parsedown::class);
        }

        $engines->register('md', function () use ($markdown) {
            return new MarkdownEngine($markdown);
        });
    }
}
