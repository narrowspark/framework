<?php
declare(strict_types=1);
namespace Viserio\Component\Profiler\Provider;

use Interop\Container\ServiceProvider;
use Interop\Http\Factory\StreamFactoryInterface;
use Psr\Cache\CacheItemPoolInterface;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface as PsrLoggerInterface;
use Viserio\Component\Contracts\OptionsResolver\ProvidesDefaultOptions as ProvidesDefaultOptionsContract;
use Viserio\Component\Contracts\OptionsResolver\RequiresComponentConfig as RequiresComponentConfigContract;
use Viserio\Component\Contracts\OptionsResolver\RequiresConfig as RequiresConfigContract;
use Viserio\Component\Contracts\OptionsResolver\RequiresMandatoryOptions as RequiresMandatoryOptionsContract;
use Viserio\Component\Contracts\Profiler\Profiler as ProfilerContract;
use Viserio\Component\Contracts\Routing\Router as RouterContract;
use Viserio\Component\Contracts\Routing\UrlGenerator as UrlGeneratorContract;
use Viserio\Component\OptionsResolver\Traits\StaticOptionsResolverTrait;
use Viserio\Component\Profiler\AssetsRenderer;
use Viserio\Component\Profiler\DataCollector\AjaxRequestsDataCollector;
use Viserio\Component\Profiler\DataCollector\MemoryDataCollector;
use Viserio\Component\Profiler\DataCollector\PhpInfoDataCollector;
use Viserio\Component\Profiler\DataCollector\TimeDataCollector;
use Viserio\Component\Profiler\Profiler;

class ProfilerServiceProvider implements
    ServiceProvider,
    RequiresComponentConfigContract,
    ProvidesDefaultOptionsContract,
    RequiresMandatoryOptionsContract
{
    use StaticOptionsResolverTrait;

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
        $options  = self::resolveOptions($container);
        $profiler = new Profiler($container->get(AssetsRenderer::class));

        if ($options['enable']) {
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

        self::registerCollectorsFromConfig($container, $profiler, $options);
        self::registerBaseCollectors($container, $profiler);

        return $profiler;
    }

    /**
     * Create a new AssetsRenderer instance.
     *
     * @param \Psr\Container\ContainerInterface $container
     *
     * @return \Viserio\Component\Profiler\AssetsRenderer
     */
    public static function createAssetsRenderer(ContainerInterface $container): AssetsRenderer
    {
        $options = self::resolveOptions($container);

        return new AssetsRenderer($options['jquery_is_used'], $options['path']);
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
                    'namespace' => 'Viserio\Component\Profiler\Controller',
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
        $options = self::resolveOptions($container);

        if ($options['collector']['time']) {
            $profiler->addCollector(new TimeDataCollector(
                $container->get(ServerRequestInterface::class)
            ));
        }

        if ($options['collector']['memory']) {
            $profiler->addCollector(new MemoryDataCollector());
        }

        if ($options['collector']['ajax']) {
            $profiler->addCollector(new AjaxRequestsDataCollector());
        }

        if ($options['collector']['phpinfo']) {
            $profiler->addCollector(new PhpInfoDataCollector());
        }
    }

    /**
     * {@inheritdoc}
     */
    protected static function getConfigClass(): RequiresConfigContract
    {
        return new self();
    }

    /**
     * Register all found collectors in config.
     *
     * @param \Psr\Container\ContainerInterface              $container
     * @param \Viserio\Component\Contracts\Profiler\Profiler $profiler
     * @param array                                          $options
     *
     * @return void
     */
    private static function registerCollectorsFromConfig(ContainerInterface $container, ProfilerContract $profiler, $options): void
    {
        if ($collectors = $options['collectors']) {
            foreach ($collectors as $collector) {
                $profiler->addCollector($container->get($collector));
            }
        }
    }
}
