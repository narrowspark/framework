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
// use Viserio\Contract\OptionsResolver\ProvidesDefaultOptions as ProvidesDefaultOptionsContract;
// use Viserio\Contract\OptionsResolver\RequiresComponentConfig as RequiresComponentConfigContract;
// use Viserio\Contract\Profiler\Profiler as ProfilerContract;
// use Viserio\Contract\Translation\Translator as TranslatorContract;
// use Viserio\Component\OptionsResolver\Traits\OptionsResolverTrait;
// use Viserio\Component\Translation\DataCollector\ViserioTranslationDataCollector;
//
// class TranslationDataCollectorServiceProvider implements
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
//                'translation' => false,
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
//            if ($options['collector']['translation']) {
//                $profiler->addCollector(new ViserioTranslationDataCollector(
//                    $container->get(TranslatorContract::class)
//                ));
//            }
//        }
//
//        return $profiler;
//    }
// }
