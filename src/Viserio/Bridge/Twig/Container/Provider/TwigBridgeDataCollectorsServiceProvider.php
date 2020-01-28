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
// use Twig\Environment as TwigEnvironment;
// use Twig\Extension\ProfilerExtension;
// use Twig\Profiler\Profile;
// use Viserio\Bridge\Twig\DataCollector\TwigDataCollector;
// use Viserio\Contract\Container\ServiceProvider as ServiceProviderContract;
// use Viserio\Contract\Config\ProvidesDefaultConfig as ProvidesDefaultConfigContract;
// use Viserio\Contract\Config\RequiresComponentConfig as RequiresComponentConfigContract;
// use Viserio\Contract\Profiler\Profiler as ProfilerContract;
// use Viserio\Component\Config\Traits\OptionsResolverTrait;
//
// class TwigBridgeDataCollectorsServiceProvider implements
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
//        return [
//            Profile::class => static function (): Profile {
//                return new Profile();
//            },
//        ];
//    }
//
//    /**
//     * {@inheritdoc}
//     */
//    public function getExtensions(): array
//    {
//        return [
//            TwigEnvironment::class  => [self::class, 'extendTwigEnvironment'],
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
//                'twig' => false,
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
//            if ($options['collector']['twig'] === true) {
//                $profiler->addCollector(new TwigDataCollector(
//                    $container->get(Profile::class),
//                    $container->get(TwigEnvironment::class)
//                ));
//            }
//        }
//
//        return $profiler;
//    }
//
//    /**
//     * Wrap Twig Environment.
//     *
//     * @param \Psr\Container\ContainerInterface $container
//     * @param null|\Twig\Environment            $twig
//     *
//     * @return null|\Twig\Environment
//     */
//    public static function extendTwigEnvironment(
//        ContainerInterface $container,
//        ?TwigEnvironment $twig = null
//    ): ?TwigEnvironment {
//        if ($twig !== null) {
//            $options = self::resolveOptions($container->get('config'));
//
//            if ($options['collector']['twig'] === true) {
//                $twig->addExtension(new ProfilerExtension(
//                    $container->get(Profile::class)
//                ));
//            }
//        }
//
//        return $twig;
//    }
// }
