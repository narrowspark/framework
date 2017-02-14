<?php
declare(strict_types=1);
namespace Viserio\Component\WebProfiler\Providers;

use Interop\Container\ContainerInterface;
use Interop\Container\ServiceProvider;
use Interop\Http\Factory\StreamFactoryInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface as PsrLoggerInterface;
use Viserio\Component\Contracts\OptionsResolver\ProvidesDefaultOptions as ProvidesDefaultOptionsContract;
use Viserio\Component\Contracts\OptionsResolver\RequiresComponentConfig as RequiresComponentConfigContract;
use Viserio\Component\Contracts\OptionsResolver\RequiresMandatoryOptions as RequiresMandatoryOptionsContract;
use Viserio\Component\Contracts\Routing\Router as RouterContract;
use Viserio\Component\Contracts\Routing\UrlGenerator as UrlGeneratorContract;
use Viserio\Component\Contracts\WebProfiler\WebProfiler as WebProfilerContract;
use Viserio\Component\OptionsResolver\OptionsResolver;
use Viserio\Component\WebProfiler\AssetsRenderer;
use Viserio\Component\WebProfiler\DataCollectors\AjaxRequestsDataCollector;
use Viserio\Component\WebProfiler\DataCollectors\MemoryDataCollector;
use Viserio\Component\WebProfiler\DataCollectors\PhpInfoDataCollector;
use Viserio\Component\WebProfiler\DataCollectors\TimeDataCollector;
use Viserio\Component\WebProfiler\WebProfiler;

class WebProfilerServiceProvider implements ServiceProvider, RequiresComponentConfigContract, ProvidesDefaultOptionsContract, RequiresMandatoryOptionsContract
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
            RouterContract::class      => [self::class, 'registerWebProfilerAssetsControllers'],
            AssetsRenderer::class      => [self::class, 'createAssetsRenderer'],
            WebProfilerContract::class => [self::class, 'createWebProfiler'],
            WebProfiler::class         => function (ContainerInterface $container) {
                return $container->get(WebProfilerContract::class);
            },
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getDimensions(): iterable
    {
        return ['viserio', 'webprofiler'];
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

    public static function createWebProfiler(ContainerInterface $container): WebProfilerContract
    {
        self::resolveOptions($container);

        $profiler = new WebProfiler($container->get(AssetsRenderer::class));

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

    public static function registerWebProfilerAssetsControllers(ContainerInterface $container): RouterContract
    {
        $router = $container->get(RouterContract::class);

        $router->group(
            [
                'namespace' => 'Viserio\Component\WebProfiler\Controllers',
                'prefix'    => 'webprofiler',
            ],
            function ($router) {
                $router->get('assets/stylesheets', [
                    'uses' => 'AssetController@css',
                    'as'   => 'webprofiler.assets.css',
                ]);
                $router->get('assets/javascript', [
                    'uses' => 'AssetController@js',
                    'as'   => 'webprofiler.assets.js',
                ]);
            }
        );

        return $router;
    }

    protected static function registerCollectors(ContainerInterface $container, WebProfiler $profiler)
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

    private static function registerCollectorsFromConfig(ContainerInterface $container, WebProfiler $profiler)
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
        if (self::$options === null) {
            self::$options = $container->get(OptionsResolver::class)
                ->configure(new static(), $container)
                ->resolve();
        }
    }
}
