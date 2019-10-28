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

namespace Viserio\Provider\Debug\Container\Provider;

use Symfony\Component\VarDumper\Caster\ReflectionCaster;
use Symfony\Component\VarDumper\Cloner\ClonerInterface;
use Symfony\Component\VarDumper\Cloner\VarCloner;
use Symfony\Component\VarDumper\Dumper\CliDumper;
use Symfony\Component\VarDumper\Dumper\DataDumperInterface;
use Symfony\Component\VarDumper\Dumper\HtmlDumper as SymfonyHtmlDumper;
use Symfony\Component\VarDumper\VarDumper;
use Twig\Environment as TwigEnvironment;
use Viserio\Bridge\Twig\Extension\DumpExtension;
use Viserio\Component\Container\Definition\ReferenceDefinition;
use Viserio\Component\OptionsResolver\Container\Definition\OptionDefinition;
use Viserio\Contract\Container\Definition\ObjectDefinition as ObjectDefinitionContract;
use Viserio\Contract\Container\ServiceProvider\AliasServiceProvider as AliasServiceProviderContract;
use Viserio\Contract\Container\ServiceProvider\ContainerBuilder as ContainerBuilderContract;
use Viserio\Contract\Container\ServiceProvider\ExtendServiceProvider as ExtendServiceProviderContract;
use Viserio\Contract\Container\ServiceProvider\ServiceProvider as ServiceProviderContract;
use Viserio\Contract\OptionsResolver\ProvidesDefaultOption as ProvidesDefaultOptionContract;
use Viserio\Contract\OptionsResolver\RequiresComponentConfig as RequiresComponentConfigContract;
use Viserio\Provider\Debug\HtmlDumper;
use Viserio\Provider\Debug\Style;

class DebugServiceProvider implements AliasServiceProviderContract,
    ExtendServiceProviderContract,
    ProvidesDefaultOptionContract,
    RequiresComponentConfigContract,
    ServiceProviderContract
{
    /**
     * {@inheritdoc}
     */
    public function build(ContainerBuilderContract $container): void
    {
        $container->singleton(VarDumper::class);
        $container->singleton(CliDumper::class);

        $container->singleton(ClonerInterface::class, VarCloner::class)
            ->addMethodCall('setMaxItems', [new OptionDefinition('max_items', self::class)])
            ->addMethodCall('setMinDepth', [new OptionDefinition('min_depth', self::class)])
            ->addMethodCall('setMaxString', [new OptionDefinition('max_string_length', self::class)])
            ->addMethodCall('addCasters', [ReflectionCaster::UNSET_CLOSURE_FILE_INFO]);

        $container->singleton(DataDumperInterface::class, HtmlDumper::class)
            ->addMethodCall(
                'addTheme',
                [
                    'narrowspark',
                    Style::NARROWSPARK_THEME,
                ]
            )
            ->addMethodCall('setTheme', [new OptionDefinition('theme', self::class)]);
    }

    /**
     * {@inheritdoc}
     */
    public function getExtensions(): array
    {
        return [
            TwigEnvironment::class => static function (ObjectDefinitionContract $definition): void {
                $definition->addMethodCall('addExtension', [new ReferenceDefinition(DumpExtension::class, ReferenceDefinition::IGNORE_ON_INVALID_REFERENCE)]);
            },
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getAlias(): array
    {
        return [
            VarCloner::class => ClonerInterface::class,
            SymfonyHtmlDumper::class => DataDumperInterface::class,
            HtmlDumper::class => DataDumperInterface::class,
        ];
    }

    /**
     * {@inheritdoc}
     */
    public static function getDimensions(): array
    {
        return ['viserio', 'debug'];
    }

    /**
     * {@inheritdoc}
     */
    public static function getDefaultOptions(): array
    {
        return [
            'max_items' => 2500,
            'min_depth' => 1,
            'max_string_length' => -1,
            'theme' => 'narrowspark',
        ];
    }
}
