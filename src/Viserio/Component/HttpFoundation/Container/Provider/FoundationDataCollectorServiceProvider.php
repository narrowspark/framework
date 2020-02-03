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
// use Viserio\Contract\Foundation\Kernel as KernelContract;
// use Viserio\Contract\Config\ProvidesDefaultConfig as ProvidesDefaultConfigContract;
// use Viserio\Contract\Config\RequiresComponentConfig as RequiresComponentConfigContract;
// use Viserio\Contract\Profiler\Profiler as ProfilerContract;
// use Viserio\Contract\Routing\Router as RouterContract;
// use Viserio\Component\HttpFoundation\DataCollector\ViserioHttpDataCollector;
// use Viserio\Component\Config\Traits\OptionsResolverTrait;
//
// class FoundationDataCollectorServiceProvider implements
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
//                'viserio_http' => false,
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
//            $kernel  = $container->get(KernelContract::class);
//
//            if ($options['collector']['viserio_http']) {
//                $profiler->addCollector(
//                    new ViserioHttpDataCollector(
//                        $container->get(RouterContract::class),
//                        $kernel->getRoutesPath()
//                    ),
//                    1
//                );
//            }
//        }
//
//        return $profiler;
//    }
// }
