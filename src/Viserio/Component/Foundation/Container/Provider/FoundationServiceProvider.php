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
use Viserio\Component\Container\Definition\ReferenceDefinition;
use Viserio\Component\OptionsResolver\Container\Definition\OptionDefinition;
use Viserio\Contract\Container\ServiceProvider\ContainerBuilder as ContainerBuilderContract;
use Viserio\Contract\Container\ServiceProvider\ServiceProvider as ServiceProviderContract;
use Viserio\Contract\OptionsResolver\RequiresComponentConfig as RequiresComponentConfigContract;
use Viserio\Contract\OptionsResolver\RequiresMandatoryOption as RequiresMandatoryOptionContract;
use Viserio\Contract\OptionsResolver\RequiresValidatedOption as RequiresValidatedOptionContract;

class FoundationServiceProvider implements RequiresComponentConfigContract,
    RequiresMandatoryOptionContract,
    RequiresValidatedOptionContract,
    ServiceProviderContract
{
    /**
     * {@inheritdoc}
     */
    public static function getDimensions(): array
    {
        return ['viserio', 'app'];
    }

    /**
     * {@inheritdoc}
     */
    public static function getMandatoryOptions(): array
    {
        return [
            'url',
        ];
    }

    /**
     * {@inheritdoc}
     */
    public static function getOptionValidators(): array
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
        if (! $container->has(ServerRequestInterface::class) && (\getenv('APP_RUNNING_IN_CONSOLE') ?? \in_array(\PHP_SAPI, ['cli', 'phpdbg', 'embed'], true))) {
            $container->singleton(ServerRequestInterface::class, [new ReferenceDefinition(ServerRequestFactoryInterface::class, ReferenceDefinition::IGNORE_ON_INVALID_REFERENCE), 'createServerRequest'])
                ->setArguments([
                    'GET',
                    new OptionDefinition('url', self::class),
                ]);
        }
    }
}
