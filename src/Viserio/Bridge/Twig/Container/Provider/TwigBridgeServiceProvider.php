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

namespace Viserio\Bridge\Twig\Container\Provider;

use Symfony\Component\VarDumper\Cloner\ClonerInterface;
use Symfony\Component\VarDumper\Dumper\HtmlDumper;
use Twig\Environment as TwigEnvironment;
use Viserio\Bridge\Twig\Command\DebugCommand;
use Viserio\Bridge\Twig\Command\LintCommand;
use Viserio\Bridge\Twig\Extension\DumpExtension;
use Viserio\Bridge\Twig\Extension\SessionExtension;
use Viserio\Bridge\Twig\Extension\StrExtension;
use Viserio\Bridge\Twig\Extension\TranslatorExtension;
use Viserio\Component\Console\Container\Pipeline\AddConsoleCommandPipe;
use Viserio\Component\Container\Definition\ReferenceDefinition;
use Viserio\Component\Support\Str;
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
            ->addTag(AddConsoleCommandPipe::TAG);
        $container->singleton(LintCommand::class)
            ->addTag(AddConsoleCommandPipe::TAG);

        $container->bind(SessionExtension::class)
            ->addArgument(new ReferenceDefinition(StoreContract::class, ReferenceDefinition::IGNORE_ON_INVALID_REFERENCE))
            ->addTag('twig.extensions');

        if ($container->has(TranslationManagerContract::class)) {
            $container->bind(TranslatorExtension::class)
                ->addTag('twig.extensions');
        }

        if (\interface_exists(ClonerInterface::class)) {
            $container->singleton(DumpExtension::class)
                ->setArguments([
                    new ReferenceDefinition(ClonerInterface::class, ReferenceDefinition::IGNORE_ON_INVALID_REFERENCE),
                    new ReferenceDefinition(HtmlDumper::class, ReferenceDefinition::IGNORE_ON_INVALID_REFERENCE),
                ]);
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
            },
        ];
    }
}
