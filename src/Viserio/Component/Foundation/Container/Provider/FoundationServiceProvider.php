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

namespace Viserio\Component\Foundation\Container\Provider;

use Psr\Http\Message\ServerRequestFactoryInterface;
use Psr\Http\Message\ServerRequestInterface;
use Viserio\Component\Config\Container\Definition\ConfigDefinition;
use Viserio\Component\Container\Definition\ReferenceDefinition;
use Viserio\Contract\Config\RequiresComponentConfig as RequiresComponentConfigContract;
use Viserio\Contract\Config\RequiresMandatoryConfig as RequiresMandatoryConfigContract;
use Viserio\Contract\Config\RequiresValidatedConfig as RequiresValidatedConfigContract;
use Viserio\Contract\Container\ServiceProvider\ContainerBuilder as ContainerBuilderContract;
use Viserio\Contract\Container\ServiceProvider\ServiceProvider as ServiceProviderContract;

class FoundationServiceProvider implements RequiresComponentConfigContract,
    RequiresMandatoryConfigContract,
    RequiresValidatedConfigContract,
    ServiceProviderContract
{
    /**
     * {@inheritdoc}
     */
    public static function getDimensions(): iterable
    {
        return ['viserio', 'app'];
    }

    /**
     * {@inheritdoc}
     */
    public static function getMandatoryConfig(): iterable
    {
        return [
            'url',
        ];
    }

    /**
     * {@inheritdoc}
     */
    public static function getConfigValidators(): iterable
    {
        return [
            'url' => ['string'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function build(ContainerBuilderContract $container): void
    {
        // @todo check this again
        if (! $container->has(ServerRequestInterface::class) && (\getenv('APP_RUNNING_IN_CONSOLE') ?? \in_array(\PHP_SAPI, ['cli', 'phpdbg', 'embed'], true))) {
            $container->singleton(ServerRequestInterface::class, [new ReferenceDefinition(ServerRequestFactoryInterface::class, ReferenceDefinition::IGNORE_ON_INVALID_REFERENCE), 'createServerRequest'])
                ->setArguments([
                    'GET',
                    (new ConfigDefinition(self::class))->setKey('url'),
                ]);
        }
    }
}
