<?php

declare(strict_types=1);

/**
 * This file is part of Narrowspark Framework.
 *
 * (c) Daniel Bannert <d.bannert@anolilab.de>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Viserio\Component\Profiler\Provider;

use Psr\Cache\CacheItemPoolInterface;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Log\LoggerInterface as PsrLoggerInterface;
use Symfony\Component\Stopwatch\Stopwatch;
use Viserio\Component\Container\Definition\ReferenceDefinition;
use Viserio\Component\OptionsResolver\Container\Definition\OptionDefinition;
use Viserio\Component\Profiler\AssetsRenderer;
use Viserio\Component\Profiler\DataCollector\AjaxRequestsDataCollector;
use Viserio\Component\Profiler\DataCollector\MemoryDataCollector;
use Viserio\Component\Profiler\DataCollector\PhpInfoDataCollector;
use Viserio\Component\Profiler\DataCollector\TimeDataCollector;
use Viserio\Component\Profiler\Profiler;
use Viserio\Contract\Config\ProvidesDefaultConfig as ProvidesDefaultConfigContract;
use Viserio\Contract\Config\RequiresComponentConfig as RequiresComponentConfigContract;
use Viserio\Contract\Config\RequiresMandatoryConfig as RequiresMandatoryConfigContract;
use Viserio\Contract\Container\ServiceProvider\AliasServiceProvider as AliasServiceProviderContract;
use Viserio\Contract\Container\ServiceProvider\ContainerBuilder as ContainerBuilderContract;
use Viserio\Contract\Container\ServiceProvider\ServiceProvider as ServiceProviderContract;
use Viserio\Contract\Events\EventManager as EventManagerContract;
use Viserio\Contract\HttpFoundation\Terminable as TerminableContract;
use Viserio\Contract\Profiler\Profiler as ProfilerContract;
use Viserio\Contract\Routing\Router as RouterContract;
use Viserio\Contract\Routing\UrlGenerator as UrlGeneratorContract;

class ProfilerServiceProvider implements AliasServiceProviderContract,
    ProvidesDefaultConfigContract,
    RequiresComponentConfigContract,
    RequiresMandatoryConfigContract,
    ServiceProviderContract
{
    /**
     * {@inheritdoc}
     */
    public function build(ContainerBuilderContract $container): void
    {
        $container->singleton(Stopwatch::class);
        $container->singleton(AssetsRenderer::class)
            ->setArguments([
                new OptionDefinition('jquery_is_used', self::class),
                new OptionDefinition('path', self::class),
            ]);

        $container->singleton(ProfilerContract::class, Profiler::class)
            ->addMethodCall('setCacheItemPool', [new ReferenceDefinition(CacheItemPoolInterface::class, ReferenceDefinition::IGNORE_ON_UNINITIALIZED_REFERENCE)])
            ->addMethodCall('setLogger', [new ReferenceDefinition(PsrLoggerInterface::class, ReferenceDefinition::IGNORE_ON_UNINITIALIZED_REFERENCE)])
            ->addMethodCall('setUrlGenerator', [new ReferenceDefinition(UrlGeneratorContract::class, ReferenceDefinition::IGNORE_ON_UNINITIALIZED_REFERENCE)])
            ->addMethodCall('setStreamFactory', [new ReferenceDefinition(StreamFactoryInterface::class, ReferenceDefinition::IGNORE_ON_UNINITIALIZED_REFERENCE)]);
    }

    /**
     * {@inheritdoc}
     */
    public function getAlias(): array
    {
        return [
            Profiler::class => ProfilerContract::class,
        ];
    }

    /**
     * {@inheritdoc}
     */
    public static function getDimensions(): iterable
    {
        return ['viserio', 'profiler'];
    }

    /**
     * {@inheritdoc}
     */
    public static function getMandatoryConfig(): iterable
    {
        return [
            'enable',
        ];
    }

    /**
     * {@inheritdoc}
     */
    public static function getDefaultConfig(): iterable
    {
        return [
            'collector' => [
                'time' => true,
                'memory' => true,
                'ajax' => false,
                'phpinfo' => false,
            ],
            'jquery_is_used' => false,
            'path' => null,
            'collectors' => [],
        ];
    }

    /**
     * Register profiler asset controllers.
     *
     * @param \Psr\Container\ContainerInterface          $container
     * @param null|\Viserio\Contract\Events\EventManager $eventManager
     *
     * @return null|\Viserio\Contract\Events\EventManager
     */
    public static function extendEventManager(
        ContainerInterface $container,
        ?EventManagerContract $eventManager = null
    ): ?EventManagerContract {
        if ($eventManager !== null) {
            $eventManager->attach(TerminableContract::TERMINATE, static function () use ($container): void {
                $container->get(ProfilerContract::class)->reset();
            });
        }

        return $eventManager;
    }

    public static function createProfiler(ContainerInterface $container): ProfilerContract
    {
        $options = self::resolveOptions($container->get('config'));
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
     * Register profiler asset controllers.
     *
     * @param \Psr\Container\ContainerInterface     $container
     * @param null|\Viserio\Contract\Routing\Router $router
     *
     * @return null|\Viserio\Contract\Routing\Router
     */
    public static function extendRouter(ContainerInterface $container, ?RouterContract $router = null): RouterContract
    {
        if ($router !== null) {
            $router->group(
                [
                    'namespace' => 'Viserio\Component\Profiler\Controller',
                    'prefix' => 'profiler',
                ],
                static function ($router): void {
                    $router->get('assets/stylesheets', [
                        'uses' => 'AssetController@css',
                        'as' => 'profiler.assets.css',
                    ]);
                    $router->get('assets/javascript', [
                        'uses' => 'AssetController@js',
                        'as' => 'profiler.assets.js',
                    ]);
                }
            );
        }

        return $router;
    }

    /**
     * Register base collectors.
     *
     * @param \Psr\Container\ContainerInterface   $container
     * @param \Viserio\Contract\Profiler\Profiler $profiler
     * @param array                               $options
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
     * @param \Psr\Container\ContainerInterface   $container
     * @param \Viserio\Contract\Profiler\Profiler $profiler
     * @param array                               $options
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
