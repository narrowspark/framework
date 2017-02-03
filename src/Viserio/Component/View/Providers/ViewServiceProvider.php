<?php
declare(strict_types=1);
namespace Viserio\Component\View\Providers;

use Interop\Container\ContainerInterface;
use Interop\Container\ServiceProvider;
use Viserio\Component\Contracts\View\Factory as FactoryContract;
use Viserio\Component\Contracts\View\Finder as FinderContract;
use Viserio\Component\View\Engines\EngineResolver;
use Viserio\Component\View\Engines\FileEngine;
use Viserio\Component\View\Engines\PhpEngine;
use Viserio\Component\View\Factory;
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
            FactoryContract::class  => [self::class, 'createViewFactory'],
            Factory::class          => function (ContainerInterface $container) {
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
        foreach (['file', 'php'] as $engineClass) {
            self::{'register' . ucfirst($engineClass) . 'Engine'}($engines, $container);
        }

        return $engines;
    }

    public static function createViewFinder(ContainerInterface $container): ViewFinder
    {
        return new ViewFinder($container);
    }

    public static function createViewFactory(ContainerInterface $container): FactoryContract
    {
        $view = new Factory(
            $container->get(EngineResolver::class),
            $container->get(ViewFinder::class)
        );

        $view->share('app', $container);

        return $view;
    }

    /**
     * Register the PHP engine implementation.
     *
     * @param \Viserio\Component\View\Engines\EngineResolver $engines
     * @param \Interop\Container\ContainerInterface          $container
     */
    protected static function registerPhpEngine(EngineResolver $engines, ContainerInterface $container)
    {
        $engines->register('php', function () {
            return new PhpEngine();
        });
    }

    /**
     * Register the File engine implementation.
     *
     * @param \Viserio\Component\View\Engines\EngineResolver $engines
     * @param \Interop\Container\ContainerInterface          $container
     */
    protected static function registerFileEngine(EngineResolver $engines, ContainerInterface $container)
    {
        $engines->register('file', function () {
            return new FileEngine();
        });
    }
}
