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

namespace Viserio\Component\Validation\Container\Provider;

use Viserio\Component\Container\Definition\ReferenceDefinition;
use Viserio\Component\Validation\Validator;
use Viserio\Contract\Container\ServiceProvider\AliasServiceProvider as AliasServiceProviderContract;
use Viserio\Contract\Container\ServiceProvider\ContainerBuilder as ContainerBuilderContract;
use Viserio\Contract\Container\ServiceProvider\ServiceProvider as ServiceProviderContract;
use Viserio\Contract\Translation\Translator as TranslatorContract;
use Viserio\Contract\Validation\Validator as ValidatorContract;

class ValidationServiceProvider implements AliasServiceProviderContract, ServiceProviderContract
{
    /**
     * {@inheritdoc}
     */
    public function build(ContainerBuilderContract $container): void
    {
        $container->singleton(ValidatorContract::class, Validator::class)
            ->addMethodCall('setTranslator', [new ReferenceDefinition(TranslatorContract::class, ReferenceDefinition::IGNORE_ON_UNINITIALIZED_REFERENCE)]);
    }

    /**
     * {@inheritdoc}
     */
    public function getAlias(): array
    {
        return [
            Validator::class => ValidatorContract::class,
            'validator' => ValidatorContract::class,
        ];
    }
}
