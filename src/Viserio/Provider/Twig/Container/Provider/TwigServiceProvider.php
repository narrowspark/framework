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

namespace Viserio\Provider\Twig\Container\Provider;

use Twig\Environment as TwigEnvironment;
use Twig\Lexer;
use Twig\Loader\ChainLoader;
use Twig\Loader\LoaderInterface;
use Twig\RuntimeLoader\ContainerRuntimeLoader;
use Twig\RuntimeLoader\RuntimeLoaderInterface;
use Viserio\Bridge\Twig\Command\LintCommand as BridgeLintCommand;
use Viserio\Component\Container\Definition\ReferenceDefinition;
use Viserio\Component\OptionsResolver\Container\Definition\OptionDefinition;
use Viserio\Contract\Container\Definition\ObjectDefinition as ObjectDefinitionContract;
use Viserio\Contract\Container\ServiceProvider\AliasServiceProvider as AliasServiceProviderContract;
use Viserio\Contract\Container\ServiceProvider\ContainerBuilder as ContainerBuilderContract;
use Viserio\Contract\Container\ServiceProvider\ExtendServiceProvider as ExtendServiceProviderContract;
use Viserio\Contract\Container\ServiceProvider\PipelineServiceProvider as PipelineServiceProviderContract;
use Viserio\Contract\Container\ServiceProvider\ServiceProvider as ServiceProviderContract;
use Viserio\Contract\OptionsResolver\ProvidesDefaultOptions as ProvidesDefaultOptionsContract;
use Viserio\Contract\OptionsResolver\RequiresComponentConfig as RequiresComponentConfigContract;
use Viserio\Contract\OptionsResolver\RequiresMandatoryOptions as RequiresMandatoryOptionsContract;
use Viserio\Contract\View\Factory as FactoryContract;
use Viserio\Contract\View\Finder as FinderContract;
use Viserio\Provider\Twig\Command\CleanCommand;
use Viserio\Provider\Twig\Command\LintCommand;
use Viserio\Provider\Twig\Container\Pipeline\RuntimeLoaderPipe;
use Viserio\Provider\Twig\Container\Pipeline\TwigLoaderPipe;
use Viserio\Provider\Twig\Engine\TwigEngine;
use Viserio\Provider\Twig\Loader as TwigLoader;

class TwigServiceProvider implements AliasServiceProviderContract,
    ExtendServiceProviderContract,
    PipelineServiceProviderContract,
    ProvidesDefaultOptionsContract,
    RequiresComponentConfigContract,
    RequiresMandatoryOptionsContract,
    ServiceProviderContract
{
    /**
     * {@inheritdoc}
     */
    public function build(ContainerBuilderContract $container): void
    {
        $container->singleton(TwigLoader::class)
            ->addMethodCall('setExtension', [new OptionDefinition('engines.twig.file_extension', self::class)]);

        $container->singleton(ChainLoader::class)
            ->addMethodCall('addLoader', [new ReferenceDefinition(TwigLoader::class)]);

        $container->singleton(RuntimeLoaderInterface::class, ContainerRuntimeLoader::class);

        $container->singleton(TwigEnvironment::class)
            ->setArguments([new ReferenceDefinition(LoaderInterface::class), new OptionDefinition('engines.twig.options', self::class)])
            ->addMethodCall('setLexer', [new ReferenceDefinition(Lexer::class, ReferenceDefinition::IGNORE_ON_UNINITIALIZED_REFERENCE)])
            ->addMethodCall('addRuntimeLoader', [new ReferenceDefinition(RuntimeLoaderInterface::class, ReferenceDefinition::IGNORE_ON_UNINITIALIZED_REFERENCE)])
            ->setPublic(true);

        $configDefinition = new ReferenceDefinition('config');

        $container->singleton(TwigEngine::class)
            ->setArguments([
                new ReferenceDefinition(TwigEnvironment::class),
                $configDefinition,
            ])
            ->addMethodCall('setContainer')
            ->addTag('view.engine');

        $container->singleton(CleanCommand::class)
            ->addArgument($configDefinition)
            ->addTag('console.command');
    }

    /**
     * {@inheritdoc}
     */
    public function getAlias(): array
    {
        return [
            LoaderInterface::class => ChainLoader::class,
            'twig' => TwigEnvironment::class,
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getExtensions(): array
    {
        return [
            FactoryContract::class => static function (ObjectDefinitionContract $definition): void {
                $definition->addMethodCall('addExtension', ['twig', 'twig']);
            },
            FinderContract::class => static function (ObjectDefinitionContract $definition, ContainerBuilderContract $container): void {
                $container->singleton(BridgeLintCommand::class, LintCommand::class)
                    ->setArguments([
                        new ReferenceDefinition(TwigEnvironment::class),
                        new ReferenceDefinition(FinderContract::class),
                        new ReferenceDefinition('config'),
                    ])
                    ->addTag('console.command');

                $container->setAlias(BridgeLintCommand::class, LintCommand::class);
            },
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getPipelines(): array
    {
        return [
            'beforeOptimization' => [
                [
                    new TwigLoaderPipe(),
                ],
            ],
            'beforeRemoving' => [
                [
                    new RuntimeLoaderPipe(),
                ],
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public static function getDimensions(): array
    {
        return ['viserio', 'view'];
    }

    /**
     * {@inheritdoc}
     */
    public static function getMandatoryOptions(): array
    {
        return [
            'paths',
            'engines' => [
                'twig' => [
                    'options' => [
                        'debug',
                    ],
                ],
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public static function getDefaultOptions(): array
    {
        return [
            'engines' => [
                'twig' => [
                    'file_extension' => 'twig',
                ],
            ],
        ];
    }
}
