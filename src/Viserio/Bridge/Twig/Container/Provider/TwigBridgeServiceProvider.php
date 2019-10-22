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

namespace Viserio\Bridge\Twig\Container\Provider;

use Twig\Environment as TwigEnvironment;
use Viserio\Bridge\Twig\Command\DebugCommand;
use Viserio\Bridge\Twig\Command\LintCommand;
use Viserio\Bridge\Twig\Extension\ConfigExtension;
use Viserio\Bridge\Twig\Extension\SessionExtension;
use Viserio\Bridge\Twig\Extension\StrExtension;
use Viserio\Bridge\Twig\Extension\TranslatorExtension;
use Viserio\Component\Container\Definition\ReferenceDefinition;
use Viserio\Component\Support\Str;
use Viserio\Contract\Config\Repository as RepositoryContract;
use Viserio\Contract\Container\Definition\ObjectDefinition as ObjectDefinitionContract;
use Viserio\Contract\Container\ServiceProvider\ContainerBuilder as ContainerBuilderContract;
use Viserio\Contract\Container\ServiceProvider\ExtendServiceProvider as ExtendServiceProviderContract;
use Viserio\Contract\Container\ServiceProvider\ServiceProvider as ServiceProviderContract;
use Viserio\Contract\Session\Store as StoreContract;
use Viserio\Contract\Translation\TranslationManager as TranslationManagerContract;

class TwigBridgeServiceProvider implements ExtendServiceProviderContract, ServiceProviderContract
{
    /**
     * {@inheritdoc}
     */
    public function build(ContainerBuilderContract $container): void
    {
        $container->singleton(DebugCommand::class)
            ->addTag('console.command');
        $container->singleton(LintCommand::class)
            ->addTag('console.command');

        if ($container->has(StoreContract::class)) {
            $container->bind(SessionExtension::class)
                ->addTag('twig.extensions');
        }

        if ($container->has(TranslationManagerContract::class)) {
            $container->bind(TranslatorExtension::class)
                ->addTag('twig.extensions');
        }

        if ($container->has(RepositoryContract::class)) {
            $container->bind(ConfigExtension::class)
                ->addTag('twig.extensions');
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getExtensions(): array
    {
        return [
            TwigEnvironment::class => static function (ObjectDefinitionContract $definition, ContainerBuilderContract $container): void {
                if (\class_exists(Str::class)) {
                    $definition->addMethodCall('addExtension', [new StrExtension()]);
                }

                if ($container->has(StoreContract::class)) {
                    $definition->addMethodCall('addExtension', [new ReferenceDefinition(SessionExtension::class)]);
                }

                if ($container->has(TranslationManagerContract::class)) {
                    $definition->addMethodCall('addExtension', [new ReferenceDefinition(TranslatorExtension::class)]);
                }

                if ($container->has(RepositoryContract::class)) {
                    $definition->addMethodCall('addExtension', [new ReferenceDefinition(ConfigExtension::class)]);
                }
            },
        ];
    }
}
