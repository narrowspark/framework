<?php
declare(strict_types=1);
namespace Viserio\Component\Profiler\Providers;

use Interop\Container\ContainerInterface;
use Interop\Container\ServiceProvider;
use Interop\Http\Factory\StreamFactoryInterface;
use Psr\Cache\CacheItemPoolInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface as PsrLoggerInterface;
use Viserio\Component\Contracts\OptionsResolver\ProvidesDefaultOptions as ProvidesDefaultOptionsContract;
use Viserio\Component\Contracts\OptionsResolver\RequiresComponentConfig as RequiresComponentConfigContract;
use Viserio\Component\Contracts\OptionsResolver\RequiresMandatoryOptions as RequiresMandatoryOptionsContract;
use Viserio\Component\Contracts\Profiler\Profiler as ProfilerContract;
use Viserio\Component\Contracts\Routing\Router as RouterContract;
use Viserio\Component\Contracts\Routing\UrlGenerator as UrlGeneratorContract;
use Viserio\Component\OptionsResolver\OptionsResolver;
use Viserio\Component\Profiler\AssetsRenderer;
use Viserio\Component\Profiler\DataCollectors\AjaxRequestsDataCollector;
use Viserio\Component\Profiler\DataCollectors\MemoryDataCollector;
use Viserio\Component\Profiler\DataCollectors\PhpInfoDataCollector;
use Viserio\Component\Profiler\DataCollectors\TimeDataCollector;
use Viserio\Component\Profiler\Profiler;

class ProfilerServiceProvider implements
    ServiceProvider,
    RequiresComponentConfigContract,
    ProvidesDefaultOptionsContract,
    RequiresMandatoryOptionsContract
{
    /**
     * Resolved cached options.
     *
     * @var array
     */
    private static $options = [];

    /**
     * {@inheritdoc}
     */
    public function getServices()
    {
        return [
            RouterContract::class      => [self::class, 'registerProfilerAssetsControllers'],
            AssetsRenderer::class      => [self::class, 'createAssetsRenderer'],
            ProfilerContract::class    => [self::class, 'createProfiler'],
            Profiler::class            => function (ContainerInterface $container) {
                return $container->get(ProfilerContract::class);
            },
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getDimensions(): iterable
    {
        return ['viserio', 'profiler'];
    }

    /**
     * {@inheritdoc}
     */
    public function getMandatoryOptions(): iterable
    {
        return [
            'enable',
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getDefaultOptions(): iterable
    {
        return [
            'collector' => [
                'time'    => true,
                'memory'  => true,
                'ajax'    => false,
                'phpinfo' => false,
            ],
            'jquery_is_used' => false,
            'path'           => null,
            'collectors'     => null,
        ];
    }

    public static function createProfiler(ContainerInterface $container): ProfilerContract
    {
        self::resolveOptions($container);

        $profiler = new Profiler($container->get(AssetsRenderer::class));

        if (self::$options['enable']) {
            $profiler->enable();
        }

        if ($container->has(CacheItemPoolInterface::class)) {
            $profiler->setCacheItemPool($container->get(CacheItemPoolInterface::class));
        }

        if ($container->has(PsrLoggerInterface::class)) {
            $profiler->setLogger($container->get(PsrLoggerInterface::class));
        }

        $profiler->setStreamFactory(
            $container->get(StreamFactoryInterface::class)
        );

        if ($container->has(UrlGeneratorContract::class)) {
            // $profiler->setUrlGenerator(
            //     $container->get(UrlGeneratorContract::class)
            // );
        }

        self::registerCollectorsFromConfig($container, $profiler);
        self::registerCollectors($container, $profiler);

        return $profiler;
    }

    public static function createAssetsRenderer(ContainerInterface $container): AssetsRenderer
    {
        self::resolveOptions($container);

        return new AssetsRenderer(
            self::$options['jquery_is_used'],
            self::$options['path']
        );
    }

    public static function registerProfilerAssetsControllers(ContainerInterface $container): RouterContract
    {
        $router = $container->get(RouterContract::class);

        $router->group(
            [
                'namespace' => 'Viserio\Component\Profiler\Controllers',
                'prefix'    => 'Profiler',
            ],
            function ($router) {
                $router->get('assets/stylesheets', [
                    'uses' => 'AssetController@css',
                    'as'   => 'Profiler.assets.css',
                ]);
                $router->get('assets/javascript', [
                    'uses' => 'AssetController@js',
                    'as'   => 'Profiler.assets.js',
                ]);
            }
        );

        return $router;
    }

    protected static function registerCollectors(ContainerInterface $container, Profiler $profiler)
    {
        self::resolveOptions($container);

        if (self::$options['collector']['time']) {
            $profiler->addCollector(new TimeDataCollector(
                $container->get(ServerRequestInterface::class)
            ));
        }

        if (self::$options['collector']['memory']) {
            $profiler->addCollector(new MemoryDataCollector());
        }

        if (self::$options['collector']['ajax']) {
            $profiler->addCollector(new AjaxRequestsDataCollector());
        }

        if (self::$options['collector']['phpinfo']) {
            $profiler->addCollector(new PhpInfoDataCollector());
        }
    }

    private static function registerCollectorsFromConfig(ContainerInterface $container, Profiler $profiler)
    {
        if (($collectors = self::$options['collectors']) !== null) {
            foreach ($collectors as $collector) {
                $profiler->addCollector($container->get($collector));
            }
        }
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
        if (count(self::$options) === 0) {
            self::$options = $container->get(OptionsResolver::class)
                ->configure(new static(), $container)
                ->resolve();
        }
    }
}
