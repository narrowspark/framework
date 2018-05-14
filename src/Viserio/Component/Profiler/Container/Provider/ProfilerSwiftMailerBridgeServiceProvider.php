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
// use Swift_Mailer;
// use Viserio\Contract\Container\ServiceProvider as ServiceProviderContract;
// use Viserio\Contract\Profiler\Profiler as ProfilerContract;
// use Viserio\Component\Profiler\DataCollector\Bridge\SwiftMailDataCollector;
//
// class ProfilerSwiftMailerBridgeServiceProvider implements ServiceProviderContract
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
//            ProfilerContract::class => [self::class, 'extendProfiler'],
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
//            $profiler->addCollector(new SwiftMailDataCollector(
//                $container->get(Swift_Mailer::class)
//            ));
//        }
//
//        return $profiler;
//    }
// }
