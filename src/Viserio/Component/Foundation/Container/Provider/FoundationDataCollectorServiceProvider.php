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
// use Viserio\Contract\OptionsResolver\ProvidesDefaultOptions as ProvidesDefaultOptionsContract;
// use Viserio\Contract\OptionsResolver\RequiresComponentConfig as RequiresComponentConfigContract;
// use Viserio\Contract\Profiler\Profiler as ProfilerContract;
// use Viserio\Component\Foundation\DataCollector\FilesLoadedCollector;
// use Viserio\Component\Foundation\DataCollector\NarrowsparkDataCollector;
// use Viserio\Component\OptionsResolver\Traits\OptionsResolverTrait;
//
// class FoundationDataCollectorServiceProvider implements
//    ServiceProviderContract,
//    RequiresComponentConfigContract,
//    ProvidesDefaultOptionsContract
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
//    public static function getDimensions(): array
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
//                'narrowspark' => false,
//                'files'       => false,
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
//            if ($options['collector']['narrowspark']) {
//                $profiler->addCollector(new NarrowsparkDataCollector($kernel->getEnvironment(), $kernel->isDebug()), -100);
//            }
//
//            if ($options['collector']['files']) {
//                $profiler->addCollector(new FilesLoadedCollector($kernel->getRootDir()));
//            }
//        }
//
//        return $profiler;
//    }
// }
