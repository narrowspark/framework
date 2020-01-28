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

//
// use Psr\Container\ContainerInterface;
// use Viserio\Contract\Container\ServiceProvider as ServiceProviderContract;
// use Viserio\Contract\Config\ProvidesDefaultConfig as ProvidesDefaultConfigContract;
// use Viserio\Contract\Config\RequiresComponentConfig as RequiresComponentConfigContract;
// use Viserio\Contract\Profiler\Profiler as ProfilerContract;
// use Viserio\Contract\Routing\Router as RouterContract;
// use Viserio\Component\Config\Traits\OptionsResolverTrait;
// use Viserio\Component\Routing\DataCollector\RoutingDataCollector;
//
// class RoutingDataCollectorServiceProvider implements
//    ServiceProviderContract,
//    RequiresComponentConfigContract,
//    ProvidesDefaultConfigContract
// {
//    use OptionsResolverTrait;
//
//    /**
//     * {@inheritdoc}
//     */
//    public function getFactories(): array
//    {
//        return [];
//    }
//
//    /**
//     * {@inheritdoc}
//     */
//    public function getExtensions(): array
//    {
//        return [
//            ProfilerContract::class => [self::class, 'extendProfiler'],
//        ];
//    }
//
//    /**
//     * {@inheritdoc}
//     */
//    public static function getDimensions(): iterable
//    {
//        return ['viserio', 'profiler'];
//    }
//
//    /**
//     * {@inheritdoc}
//     */
//    public static function getDefaultOptions(): array
//    {
//        return [
//            'collector' => [
//                'routes' => false,
//            ],
//        ];
//    }
//
//    /**
//     * Extend viserio profiler with data collector.
//     *
//     * @param \Psr\Container\ContainerInterface                  $container
//     * @param null|\Viserio\Contract\Profiler\Profiler $profiler
//     *
//     * @return null|\Viserio\Contract\Profiler\Profiler
//     */
//    public static function extendProfiler(
//        ContainerInterface $container,
//        ?ProfilerContract $profiler = null
//    ): ?ProfilerContract {
//        if ($profiler !== null) {
//            $options = self::resolveOptions($container->get('config'));
//
//            if ($options['collector']['routes']) {
//                $profiler->addCollector(
//                    new RoutingDataCollector(
//                        $container->get(RouterContract::class)->getRoutes()
//                    )
//                );
//            }
//        }
//
//        return $profiler;
//    }
// }
