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
use Viserio\Component\Config\Container\Definition\ConfigDefinition;
use Viserio\Component\Console\Container\Pipeline\AddConsoleCommandPipe;
use Viserio\Component\Container\Definition\ReferenceDefinition;
use Viserio\Contract\Config\ProvidesDefaultConfig as ProvidesDefaultConfigContract;
use Viserio\Contract\Config\RequiresComponentConfig as RequiresComponentConfigContract;
use Viserio\Contract\Config\RequiresMandatoryConfig as RequiresMandatoryConfigContract;
use Viserio\Contract\Container\Definition\ObjectDefinition as ObjectDefinitionContract;
use Viserio\Contract\Container\ServiceProvider\AliasServiceProvider as AliasServiceProviderContract;
use Viserio\Contract\Container\ServiceProvider\ContainerBuilder as ContainerBuilderContract;
use Viserio\Contract\Container\ServiceProvider\ExtendServiceProvider as ExtendServiceProviderContract;
use Viserio\Contract\Container\ServiceProvider\PipelineServiceProvider as PipelineServiceProviderContract;
use Viserio\Contract\Container\ServiceProvider\ServiceProvider as ServiceProviderContract;
use Viserio\Contract\Filesystem\Filesystem as FilesystemContract;
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
    public static function getDefaultConfig(): iterable
    {
        return [
            'engines' => [
                'twig' => [
                    'file_extension' => 'twig',
                ],
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function build(ContainerBuilderContract $container): void
    {
        $container->singleton(TwigLoader::class)
            ->addMethodCall('setExtension', [
                (new ConfigDefinition(self::class))
                    ->setKey('engines.twig.file_extension'),
            ]);

        $container->singleton(ChainLoader::class)
            ->addMethodCall('addLoader', [new ReferenceDefinition(TwigLoader::class)]);

        $container->singleton(RuntimeLoaderInterface::class, ContainerRuntimeLoader::class);

        $container->singleton(TwigEnvironment::class)
            ->setArguments([
                new ReferenceDefinition(LoaderInterface::class),
                (new ConfigDefinition(self::class))
                    ->setKey('engines.twig.options'),
            ])
            ->addMethodCall('setLexer', [new ReferenceDefinition(Lexer::class, ReferenceDefinition::IGNORE_ON_UNINITIALIZED_REFERENCE)])
            ->addMethodCall('addRuntimeLoader', [new ReferenceDefinition(RuntimeLoaderInterface::class, ReferenceDefinition::IGNORE_ON_UNINITIALIZED_REFERENCE)])
            ->setPublic(true);

        $container->singleton(TwigEngine::class)
            ->setArguments([
                new ReferenceDefinition(TwigEnvironment::class),
                (new ConfigDefinition(TwigEngine::class))
                    ->setKey('engines.twig.extensions'),
            ])
            ->addMethodCall('setContainer')
            ->addTag('view.engine');

        $container->singleton(CleanCommand::class)
            ->setArguments([
                new ReferenceDefinition(FilesystemContract::class),
                (new ConfigDefinition(CleanCommand::class))
                    ->setKey('engines.twig.options.cache'),
            ])
            ->addTag(AddConsoleCommandPipe::TAG);
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
                        (new ConfigDefinition(LintCommand::class))
                            ->setKey('engines.twig.file_extension'),
                    ])
                    ->addTag(AddConsoleCommandPipe::TAG);

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
}
