<?php
declare(strict_types=1);
namespace Viserio\View\Providers;

use Interop\Container\ContainerInterface;
use Interop\Container\ServiceProvider;
use Psr\Http\Message\ServerRequestInterface;
use Viserio\Config\Manager as ConfigManager;
use Viserio\Filesystem\Filesystem;
use Viserio\View\Engines\Adapter\Php as PhpEngine;
use Viserio\View\Engines\Adapter\Plates as PlatesEngine;
use Viserio\View\Engines\Adapter\Twig as TwigEngine;
use Viserio\View\Engines\EngineResolver;
use Viserio\View\Factory;
use Viserio\View\ViewFinder;

class ViewServiceProvider implements ServiceProvider
{
    /**
     * {@inheritdoc}
     */
    public function getServices()
    {
        return [
            EngineResolver::class => [self::class, 'createEngineResolver'],
            'view.engine.resolver' => function (ContainerInterface $container) {
                return $container->get(EngineResolver::class);
            },
            ViewFinder::class => [self::class, 'createViewFinder'],
            'view.finder' => function (ContainerInterface $container) {
                return $container->get(ViewFinder::class);
            },
            Factory::class => [self::class, 'createViewFactory'],
            'view' => function (ContainerInterface $container) {
                return $container->get(Factory::class);
            },
        ];
    }

    public static function createEngineResolver(ContainerInterface $container)
    {
        $engines = new EngineResolver();

        // Next we will register the various engines with the engines so that the
        // environment can resolve the engines it needs for various views based
        // on the extension of view files. We call a method for each engines.
        foreach (['php', 'twig', 'plates'] as $engineClass) {
            self::{'register' . ucfirst($engineClass) . 'Engine'}($engines, $container);
        }

        if (($compilers = $container->get(ConfigManager::class)->get('view.compilers')) !== null) {
            foreach ($compilers as $compilerName => $compilerClass) {
                if ($compilerName === $compilerClass[0]) {
                    self::registercustomEngine(
                        $compilerName,
                        call_user_func_array($compilerClass[0], (array) $compilerClass[1]),
                        $engines
                    );
                }
            }
        }

        return $engines;
    }

    public static function createViewFinder(ContainerInterface $container)
    {
        return new ViewFinder(
            $container->get(Filesystem::class),
            $container->get(ConfigManager::class)->get('view.template.paths', [])
        );
    }

    public static function createViewFactory(ContainerInterface $container)
    {
        $view = new Factory(
            $container->get(EngineResolver::class),
            $container->get(ViewFinder::class)
        );

        $view->share('app', $container);

        return $view;
    }

    /**
     * Register custom engine implementation.
     *
     * @param string                               $engineName
     * @param string                               $engineClass
     * @param \Viserio\View\Engines\EngineResolver $engines
     */
    protected static function registercustomEngine(string $engineName, string $engineClass, EngineResolver $engines)
    {
        $engines->register($engineName, function () use ($engineClass) {
            return $engineClass;
        });
    }

    /**
     * Register the PHP engine implementation.
     *
     * @param \Viserio\View\Engines\EngineResolver $engines
     */
    protected static function registerPhpEngine(EngineResolver $engines, ContainerInterface $container)
    {
        $engines->register('php', function () {
            return new PhpEngine();
        });
    }

    /**
     * Register the PHP engine implementation.
     *
     * @param \Viserio\View\Engines\EngineResolver $engines
     */
    protected static function registerTwigEngine(EngineResolver $engines, ContainerInterface $container)
    {
        $engines->register('twig', function () {
            return new TwigEngine($container->get(ConfigManager::class));
        });
    }

    /**
     * Register the PHP engine implementation.
     *
     * @param \Viserio\View\Engines\EngineResolver $engines
     */
    protected static function registerPlatesEngine(EngineResolver $engines, ContainerInterface $container)
    {
        $request = null;

        if ($container->has(ServerRequestInterface::class)) {
            $request = $container->get(ServerRequestInterface::class);
        }

        $engines->register('plates', function () {
            return new PlatesEngine(
                $container->get(ConfigManager::class),
                $request
            );
        });
    }
}
