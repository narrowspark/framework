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

namespace Viserio\Component\View\Container\Provider;

use Parsedown;
use ParsedownExtra;
use Psr\Container\ContainerInterface;
use Viserio\Component\Container\Definition\ReferenceDefinition;
use Viserio\Component\Container\PipelineConfig;
use Viserio\Component\View\Container\Pipeline\AddViewEnginePipe;
use Viserio\Component\View\Engine\FileEngine;
use Viserio\Component\View\Engine\MarkdownEngine;
use Viserio\Component\View\Engine\PhpEngine;
use Viserio\Component\View\ViewFactory;
use Viserio\Component\View\ViewFinder;
use Viserio\Contract\Container\ServiceProvider\AliasServiceProvider as AliasServiceProviderContract;
use Viserio\Contract\Container\ServiceProvider\ContainerBuilder as ContainerBuilderContract;
use Viserio\Contract\Container\ServiceProvider\PipelineServiceProvider as PipelineServiceProviderContract;
use Viserio\Contract\Container\ServiceProvider\ServiceProvider as ServiceProviderContract;
use Viserio\Contract\View\EngineResolver as EngineResolverContract;
use Viserio\Contract\View\Factory as FactoryContract;
use Viserio\Contract\View\Finder as FinderContract;

class ViewServiceProvider implements AliasServiceProviderContract,
    PipelineServiceProviderContract,
    ServiceProviderContract
{
    /**
     * {@inheritdoc}
     */
    public function build(ContainerBuilderContract $container): void
    {
        $container->singleton(FinderContract::class, ViewFinder::class)
            ->setArguments([new ReferenceDefinition('config')]);
        $container->singleton(FactoryContract::class, ViewFactory::class)
            ->setArguments([new ReferenceDefinition(EngineResolverContract::class), new ReferenceDefinition(FinderContract::class)])
            ->addMethodCall('share', ['app', new ReferenceDefinition(ContainerInterface::class)])
            ->setPublic(true);

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
