<?php
declare(strict_types=1);
namespace Viserio\Component\Profiler\Providers;

use Interop\Container\ServiceProvider;
use Interop\Http\Factory\StreamFactoryInterface;
use Psr\Cache\CacheItemPoolInterface;
use Psr\Container\ContainerInterface;
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
            RouterContract::class   => [self::class, 'registerProfilerAssetsControllers'],
            AssetsRenderer::class   => [self::class, 'createAssetsRenderer'],
            ProfilerContract::class => [self::class, 'createProfiler'],
            Profiler::class         => function (ContainerInterface $container) {
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
            'collectors'     => [],
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
            $profiler->setUrlGenerator($container->get(UrlGeneratorContract::class));
        }

        self::registerCollectorsFromConfig($container, $profiler);
        self::registerBaseCollectors($container, $profiler);

        return $profiler;
    }

    public static function createAssetsRenderer(ContainerInterface $container): AssetsRenderer
    {
        self::resolveOptions($container);

        return new AssetsRenderer(self::$options['jquery_is_used'], self::$options['path']);
    }

    /**
     * Register profiler asset controllers.
     *
     * @param \Psr\Container\ContainerInterface $container
     * @param null|callable                     $getPrevious
     *
     * @return \Viserio\Component\Contracts\Routing\Router
     */
    public static function registerProfilerAssetsControllers(ContainerInterface $container, ?callable $getPrevious = null): RouterContract
    {
        $router = is_callable($getPrevious) ? $getPrevious() : $getPrevious;

        if ($router !== null) {
            $router->group(
                [
                    'namespace' => 'Viserio\Component\Profiler\Controllers',
                    'prefix'    => 'profiler',
                ],
                function ($router) {
                    $router->get('assets/stylesheets', [
                        'uses' => 'AssetController@css',
                        'as'   => 'profiler.assets.css',
                    ]);
                    $router->get('assets/javascript', [
                        'uses' => 'AssetController@js',
                        'as'   => 'profiler.assets.js',
                    ]);
                }
            );
        }

        return $router;
    }

    /**
     * Register base collectors.
     *
     * @param \Psr\Container\ContainerInterface              $container
     * @param \Viserio\Component\Contracts\Profiler\Profiler $profiler
     *
     * @return void
     */
    protected static function registerBaseCollectors(ContainerInterface $container, ProfilerContract $profiler): void
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

    /**
     * Register all found collectors in config.
     *
     * @param \Psr\Container\ContainerInterface              $container
     * @param \Viserio\Component\Contracts\Profiler\Profiler $profiler
     *
     * @return void
     */
    private static function registerCollectorsFromConfig(ContainerInterface $container, ProfilerContract $profiler): void
    {
        if ($collectors = self::$options['collectors']) {
            foreach ($collectors as $collector) {
                $profiler->addCollector($container->get($collector));
            }
        }
    }

    /**
     * Resolve component options.
     *
     * @param \Psr\Container\ContainerInterface $container
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
