<?php

declare(strict_types=1);

/**
 * Copyright (c) 2018-2020 Daniel Bannert
 *
 * For the full copyright and license information, please view
 * the LICENSE.md file that was distributed with this source code.
 *
 * @see https://github.com/narrowspark/automatic
 */

namespace Viserio\Component\View\Container\Provider;

use Parsedown;
use ParsedownExtra;
use Psr\Container\ContainerInterface;
use Viserio\Component\Config\Container\Definition\ConfigDefinition;
use Viserio\Component\Container\Definition\ReferenceDefinition;
use Viserio\Component\Container\Pipeline\ResolvePreloadPipe;
use Viserio\Component\Container\PipelineConfig;
use Viserio\Component\View\Container\Pipeline\AddViewEnginePipe;
use Viserio\Component\View\Engine\FileEngine;
use Viserio\Component\View\Engine\MarkdownEngine;
use Viserio\Component\View\Engine\PhpEngine;
use Viserio\Component\View\ViewFactory;
use Viserio\Component\View\ViewFinder;
use Viserio\Contract\Config\ProvidesDefaultConfig as ProvidesDefaultConfigContract;
use Viserio\Contract\Config\RequiresComponentConfig as RequiresComponentConfigContract;
use Viserio\Contract\Config\RequiresMandatoryConfig as RequiresMandatoryConfigContract;
use Viserio\Contract\Container\ServiceProvider\AliasServiceProvider as AliasServiceProviderContract;
use Viserio\Contract\Container\ServiceProvider\ContainerBuilder as ContainerBuilderContract;
use Viserio\Contract\Container\ServiceProvider\PipelineServiceProvider as PipelineServiceProviderContract;
use Viserio\Contract\Container\ServiceProvider\ServiceProvider as ServiceProviderContract;
use Viserio\Contract\View\EngineResolver as EngineResolverContract;
use Viserio\Contract\View\Factory as FactoryContract;
use Viserio\Contract\View\Finder as FinderContract;

class ViewServiceProvider implements AliasServiceProviderContract,
    PipelineServiceProviderContract,
    ProvidesDefaultConfigContract,
    RequiresComponentConfigContract,
    RequiresMandatoryConfigContract,
    ServiceProviderContract
{
    /**
     * {@inheritdoc}
     */
    public static function getDimensions(): iterable
    {
        return ['viserio', 'view'];
    }

    /**
     * {@inheritdoc}
     */
    public static function getMandatoryConfig(): iterable
    {
        return [
            'paths',
        ];
    }

    /**
     * {@inheritdoc}
     */
    public static function getDefaultConfig(): iterable
    {
        return [
            'extensions' => [],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function build(ContainerBuilderContract $container): void
    {
        $container->singleton(FinderContract::class, ViewFinder::class)
            ->setArguments([
                (new ConfigDefinition(self::class))
                    ->setKey('paths'),
                (new ConfigDefinition(self::class))
                    ->setKey('extensions'),
            ])
            ->addTag(ResolvePreloadPipe::TAG);
        $container->singleton(FactoryContract::class, ViewFactory::class)
            ->setArguments([new ReferenceDefinition(EngineResolverContract::class), new ReferenceDefinition(FinderContract::class)])
            ->addMethodCall('share', ['app', new ReferenceDefinition(ContainerInterface::class)])
            ->setPublic(true)
            ->addTag(ResolvePreloadPipe::TAG);

        $container->singleton(FileEngine::class)
            ->addTag('view.engine');
        $container->singleton(PhpEngine::class)
            ->addTag('view.engine');

        if ($container->has(Parsedown::class) || $container->has(ParsedownExtra::class)) {
            $container->singleton(MarkdownEngine::class)
                ->setArguments([new ReferenceDefinition($container->has(ParsedownExtra::class) ? ParsedownExtra::class : Parsedown::class)])
                ->addTag('view.engine');
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getAlias(): array
    {
        return [
            ViewFinder::class => FinderContract::class,
            'view.finder' => FinderContract::class,
            ViewFactory::class => FactoryContract::class,
            'view' => FactoryContract::class,
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getPipelines(): array
    {
        return [
            PipelineConfig::TYPE_BEFORE_OPTIMIZATION => [
                [
                    new AddViewEnginePipe(),
                ],
            ],
        ];
    }
}
