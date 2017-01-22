<?php
declare(strict_types=1);
namespace Viserio\Component\View\Providers;

use Interop\Container\ContainerInterface;
use Interop\Container\ServiceProvider;
use Viserio\Component\Contracts\Support\Traits\ServiceProviderConfigAwareTrait;
use Viserio\Component\Contracts\View\Factory as FactoryContract;
use Viserio\Component\Filesystem\Filesystem;
use Viserio\Component\View\Engines\EngineResolver;
use Viserio\Component\View\Engines\FileEngine;
use Viserio\Component\View\Engines\PhpEngine;
use Viserio\Component\View\Engines\PlatesEngine;
use Viserio\Component\View\Engines\TwigEngine;
use Viserio\Component\View\Factory;
use Viserio\Component\View\ViewFinder;

class ViewServiceProvider implements ServiceProvider
{
    use ServiceProviderConfigAwareTrait;

    public const PACKAGE = 'viserio.view';

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
            ViewFinder::class => [self::class, 'createViewFinder'],
            'view.finder'     => function (ContainerInterface $container) {
                return $container->get(ViewFinder::class);
            },
            Factory::class         => [self::class, 'createViewFactory'],
            FactoryContract::class => function (ContainerInterface $container) {
                return $container->get(Factory::class);
            },
            'view' => function (ContainerInterface $container) {
                return $container->get(Factory::class);
            },
        ];
    }

    public static function createEngineResolver(ContainerInterface $container): EngineResolver
    {
        $engines = new EngineResolver();

        // Next we will register the various engines with the engines so that the
        // environment can resolve the engines it needs for various views based
        // on the extension of view files. We call a method for each engines.
        foreach (['file', 'php', 'twig', 'plates'] as $engineClass) {
            self::{'register' . ucfirst($engineClass) . 'Engine'}($engines, $container);
        }

        return $engines;
    }

    public static function createViewFinder(ContainerInterface $container)
    {
        $paths = array_merge(
            [self::getConfig($container, 'template.default', '')],
            self::getConfig($container, 'template.paths', [])
        );

        return new ViewFinder(
            $container->get(Filesystem::class),
            $paths,
            self::getConfig($container, 'file_extensions', null)
        );
    }

    public static function createViewFactory(ContainerInterface $container)
    {
        $view = new Factory(
            $container->get(EngineResolver::class),
            $container->get(ViewFinder::class)
        );

        $view->share('app', $container);
        $view->addExtension('html', 'twig');
        $view->addExtension('twig.html', 'twig');
        $view->addExtension('plates.php', 'plates');

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

    /**
     * Register the PHP engine implementation.
     *
     * @param \Viserio\Component\View\Engines\EngineResolver $engines
     * @param \Interop\Container\ContainerInterface          $container
     */
    protected static function registerTwigEngine(EngineResolver $engines, ContainerInterface $container)
    {
        $engines->register('twig', function () use ($container) {
            return new TwigEngine($container);
        });
    }

    /**
     * Register the PHP engine implementation.
     *
     * @param \Viserio\Component\View\Engines\EngineResolver $engines
     * @param \Interop\Container\ContainerInterface          $container
     */
    protected static function registerPlatesEngine(EngineResolver $engines, ContainerInterface $container)
    {
        $engines->register('plates', function () use ($container) {
            return new PlatesEngine($container);
        });
    }
}
