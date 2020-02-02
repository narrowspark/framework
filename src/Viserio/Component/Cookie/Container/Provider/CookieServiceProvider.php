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

namespace Viserio\Component\Cookie\Container\Provider;

use Viserio\Component\Config\Container\Definition\ConfigDefinition;
use Viserio\Component\Cookie\CookieJar;
use Viserio\Contract\Config\ProvidesDefaultConfig as ProvidesDefaultConfigContract;
use Viserio\Contract\Config\RequiresComponentConfig as RequiresComponentConfigContract;
use Viserio\Contract\Config\RequiresMandatoryConfig as RequiresMandatoryConfigContract;
use Viserio\Contract\Container\ServiceProvider\AliasServiceProvider as AliasServiceProviderContract;
use Viserio\Contract\Container\ServiceProvider\ContainerBuilder as ContainerBuilderContract;
use Viserio\Contract\Container\ServiceProvider\ServiceProvider as ServiceProviderContract;
use Viserio\Contract\Cookie\QueueingFactory as JarContract;

class CookieServiceProvider implements AliasServiceProviderContract,
    ProvidesDefaultConfigContract,
    RequiresComponentConfigContract,
    RequiresMandatoryConfigContract,
    ServiceProviderContract
{
    /**
     * {@inheritdoc}
     */
    public function build(ContainerBuilderContract $container): void
    {
        $container->singleton(JarContract::class, CookieJar::class)
            ->addMethodCall(
                'setDefaultPathAndDomain',
                [
                    (new ConfigDefinition(self::class))
                        ->setKey('path'),
                    (new ConfigDefinition(self::class))
                        ->setKey('domain'),
                    (new ConfigDefinition(self::class))
                        ->setKey('secure'),
                ]
            );
    }

    /**
     * {@inheritdoc}
     */
    public function getAlias(): array
    {
        return [
            CookieJar::class => JarContract::class,
            'cookie' => JarContract::class,
        ];
    }

    /**
     * {@inheritdoc}
     */
    public static function getDimensions(): iterable
    {
        return ['viserio', 'cookie'];
    }

    /**
     * {@inheritdoc}
     */
    public static function getMandatoryConfig(): iterable
    {
        return ['path', 'domain'];
    }

    /**
     * {@inheritdoc}
     */
    public static function getDefaultConfig(): iterable
    {
        return [
            'secure' => true,
        ];
    }
}
