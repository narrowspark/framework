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

// namespace Viserio\Component\Events\Container\Provider;
//
// use Psr\Container\ContainerInterface;
// use Psr\Log\LoggerInterface;
// use Viserio\Component\Container\Definition\ReferenceDefinition;
// use Viserio\Contract\Container\ObjectDefinition as ObjectDefinitionContract;
// use Viserio\Contract\Container\ServiceProvider\ContainerBuilder as ContainerBuilderContract;
// use Viserio\Contract\Container\ServiceProvider\ExtendServiceProvider as ExtendServiceProviderContract;
// use Viserio\Contract\Container\ServiceProvider\ServiceProvider as ServiceProviderContract;
// use Viserio\Contract\OptionsResolver\ProvidesDefaultOption as ProvidesDefaultOptionContract;
// use Viserio\Contract\OptionsResolver\RequiresComponentConfig as RequiresComponentConfigContract;
// use Viserio\Contract\Profiler\Profiler as ProfilerContract;
// use Viserio\Component\Events\DataCollector\TraceableEventManager;
// use Viserio\Component\Events\DataCollector\ViserioEventsDataCollector;
// use Viserio\Component\OptionsResolver\Traits\OptionsResolverTrait;
//
// class EventsDataCollectorServiceProvider implements
//    ServiceProviderContract,
//    ExtendServiceProviderContract,
//    RequiresComponentConfigContract,
//    ProvidesDefaultOptionContract
// {
//    use OptionsResolverTrait;
//
//    /**
//     * {@inheritdoc}
//     */
//    public function build(ContainerBuilderContract $container): void
//    {
//        $options = self::resolveOptions($container->get('config'));
//
//        if ($options['collector']['events']) {
//            $container->singleton(TraceableEventManager::class)
//                ->addMethodCall('setLogger', [new ReferenceDefinition(LoggerInterface::class, ReferenceDefinition::IGNORE_ON_INVALID_REFERENCE)]);
//            $container->singleton(ViserioEventsDataCollector::class);
//        }
//    }
//
//    /**
//     * {@inheritdoc}
//     */
//    public function getExtensions(): array
//    {
//        return [
//            ProfilerContract::class => static function (ObjectDefinitionContract $definition, ContainerInterface $container): ObjectDefinitionContract {
//                $options = self::resolveOptions($container->get('config'));
//
//                if ($options['collector']['events']) {
//                    $definition->addMethodCall('addCollector', [new ReferenceDefinition(ViserioEventsDataCollector::class)]);
//                }
//
//                return $definition;
//            },
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
//                'events' => false,
//            ],
//        ];
//    }
// }
