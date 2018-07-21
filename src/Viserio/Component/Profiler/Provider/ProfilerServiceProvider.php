<?php
declare(strict_types=1);
namespace Viserio\Component\Profiler\Provider;

use Interop\Container\ServiceProviderInterface;
use Interop\Http\Factory\StreamFactoryInterface;
use Psr\Cache\CacheItemPoolInterface;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface as PsrLoggerInterface;
use Symfony\Component\Stopwatch\Stopwatch;
use Viserio\Component\Contract\Events\EventManager as EventManagerContract;
use Viserio\Component\Contract\Foundation\Terminable as TerminableContract;
use Viserio\Component\Contract\OptionsResolver\ProvidesDefaultOptions as ProvidesDefaultOptionsContract;
use Viserio\Component\Contract\OptionsResolver\RequiresComponentConfig as RequiresComponentConfigContract;
use Viserio\Component\Contract\OptionsResolver\RequiresMandatoryOptions as RequiresMandatoryOptionsContract;
use Viserio\Component\Contract\Profiler\Profiler as ProfilerContract;
use Viserio\Component\Contract\Routing\Router as RouterContract;
use Viserio\Component\Contract\Routing\UrlGenerator as UrlGeneratorContract;
use Viserio\Component\OptionsResolver\Traits\OptionsResolverTrait;
use Viserio\Component\Profiler\AssetsRenderer;
use Viserio\Component\Profiler\DataCollector\AjaxRequestsDataCollector;
use Viserio\Component\Profiler\DataCollector\MemoryDataCollector;
use Viserio\Component\Profiler\DataCollector\PhpInfoDataCollector;
use Viserio\Component\Profiler\DataCollector\TimeDataCollector;
use Viserio\Component\Profiler\Profiler;

class ProfilerServiceProvider implements
    ServiceProviderInterface,
    RequiresComponentConfigContract,
    ProvidesDefaultOptionsContract,
    RequiresMandatoryOptionsContract
{
    use OptionsResolverTrait;

    /**
     * {@inheritdoc}
     */
    public function getFactories(): array
    {
        return [
            AssetsRenderer::class   => [self::class, 'createAssetsRenderer'],
            ProfilerContract::class => [self::class, 'createProfiler'],
            Profiler::class         => function (ContainerInterface $container) {
                return $container->get(ProfilerContract::class);
            },
            Stopwatch::class => function () {
                return new Stopwatch();
            },
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getExtensions(): array
    {
        return [
            RouterContract::class       => [self::class, 'extendRouter'],
            EventManagerContract::class => [self::class, 'extendEventManager'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public static function getDimensions(): array
    {
        return ['viserio', 'profiler'];
    }

    /**
     * {@inheritdoc}
     */
    public static function getMandatoryOptions(): array
    {
        return [
            'enable',
        ];
    }

    /**
     * {@inheritdoc}
     */
    public static function getDefaultOptions(): array
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

    /**
     * Register profiler asset controllers.
     *
     * @param \Psr\Container\ContainerInterface                    $container
     * @param null|\Viserio\Component\Contract\Events\EventManager $eventManager
     *
     * @return null|\Viserio\Component\Contract\Events\EventManager
     */
    public static function extendEventManager(
        ContainerInterface $container,
        ?EventManagerContract $eventManager = null
    ): ?EventManagerContract {
        if ($eventManager !== null) {
            $eventManager->attach(TerminableContract::TERMINATE, function () use ($container): void {
                $container->get(ProfilerContract::class)->reset();
            });
        }

        return $eventManager;
    }

    public static function createProfiler(ContainerInterface $container): ProfilerContract
    {
        $options  = self::resolveOptions($container->get('config'));
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
        self::registerBaseCollectors($container, $profiler, $options);

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
        $options = self::resolveOptions($container->get('config'));

        return new AssetsRenderer($options['jquery_is_used'], $options['path']);
    }

    /**
     * Register profiler asset controllers.
     *
     * @param \Psr\Container\ContainerInterface               $container
     * @param null|\Viserio\Component\Contract\Routing\Router $router
     *
     * @return null|\Viserio\Component\Contract\Routing\Router
     */
    public static function extendRouter(
        ContainerInterface $container,
        ?RouterContract $router = null
    ): RouterContract {
        if ($router !== null) {
            $router->group(
                [
                    'namespace' => 'Viserio\Component\Profiler\Controller',
                    'prefix'    => 'profiler',
                ],
                function ($router): void {
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
     * @param \Psr\Container\ContainerInterface             $container
     * @param \Viserio\Component\Contract\Profiler\Profiler $profiler
     * @param array                                         $options
     *
     * @return void
     */
    protected static function registerBaseCollectors(
        ContainerInterface $container,
        ProfilerContract $profiler,
        array $options
    ): void {
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
     * Register all found collectors in config.
     *
     * @param \Psr\Container\ContainerInterface             $container
     * @param \Viserio\Component\Contract\Profiler\Profiler $profiler
     * @param array                                         $options
     *
     * @return void
     */
    private static function registerCollectorsFromConfig(
        ContainerInterface $container,
        ProfilerContract $profiler,
        array $options
    ): void {
        if ($collectors = $options['collectors']) {
            foreach ($collectors as $collector) {
                $profiler->addCollector($container->get($collector));
            }
        }
    }
}
