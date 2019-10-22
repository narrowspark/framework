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
// use Viserio\Bridge\Monolog\Processor\DebugProcessor;
// use Viserio\Contract\Container\ServiceProvider as ServiceProviderContract;
// use Viserio\Contract\OptionsResolver\ProvidesDefaultOptions as ProvidesDefaultOptionsContract;
// use Viserio\Contract\OptionsResolver\RequiresComponentConfig as RequiresComponentConfigContract;
// use Viserio\Contract\Profiler\Profiler as ProfilerContract;
// use Viserio\Component\Log\DataCollector\LoggerDataCollector;
// use Viserio\Component\Log\LogManager;
// use Viserio\Component\OptionsResolver\Traits\OptionsResolverTrait;
//
// class LoggerDataCollectorServiceProvider implements
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
//            LogManager::class       => [self::class, 'extendLogManager'],
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
//                'logs' => false,
//            ],
//        ];
//    }
//
//    /**
//     * Extend monolog with a processor.
//     *
//     * @param \Psr\Container\ContainerInterface                      $container
//     * @param null|\Monolog\Logger|\Viserio\Component\Log\LogManager $logManager
//     *
//     * @return null|\Monolog\Logger|\Viserio\Component\Log\Logger
//     */
//    public static function extendLogManager(ContainerInterface $container, $logManager = null)
//    {
//        $options = self::resolveOptions($container->get('config'));
//
//        if ($logManager !== null && $options['collector']['logs'] === true) {
//            $logManager->pushProcessor(new DebugProcessor());
//        }
//
//        return $logManager;
//    }
//
//    /**
//     * Extend viserio profiler with a data collector.
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
//            if ($options['collector']['logs'] === true && $container->has(LogManager::class)) {
//                $profiler->addCollector(new LoggerDataCollector($container->get(LogManager::class)->getDriver()));
//            }
//        }
//
//        return $profiler;
//    }
// }
