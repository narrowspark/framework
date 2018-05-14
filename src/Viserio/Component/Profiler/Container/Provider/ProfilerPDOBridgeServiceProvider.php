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
// use PDO;
// use Psr\Container\ContainerInterface;
// use Viserio\Contract\Container\ServiceProvider as ServiceProviderContract;
// use Viserio\Contract\Profiler\Profiler as ProfilerContract;
// use Viserio\Component\Profiler\DataCollector\Bridge\PDO\PDODataCollector;
// use Viserio\Component\Profiler\DataCollector\Bridge\PDO\TraceablePDODecorater;
//
// class ProfilerPDOBridgeServiceProvider implements ServiceProviderContract
// {
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
//            PDO::class                   => [self::class, 'createTraceablePDODecorator'],
//            TraceablePDODecorater::class => static function (ContainerInterface $container) {
//                return $container->get(PDO::class);
//            },
//            ProfilerContract::class => [self::class, 'extendProfiler'],
//        ];
//    }
//
//    /**
//     * Extend PDO with our TraceablePDODecorater.
//     *
//     * @param \Psr\Container\ContainerInterface $container
//     * @param null|\PDO                         $pdo
//     *
//     * @return null|\Viserio\Component\Profiler\DataCollector\Bridge\PDO\TraceablePDODecorater
//     */
//    public static function createTraceablePDODecorator(
//        ContainerInterface $container,
//        ?PDO $pdo = null
//    ): ?TraceablePDODecorater {
//        if ($pdo === null) {
//            return null;
//        }
//
//        return new TraceablePDODecorater($pdo);
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
//            $profiler->addCollector(new PDODataCollector(
//                $container->get(TraceablePDODecorater::class)
//            ));
//        }
//
//        return $profiler;
//    }
// }
