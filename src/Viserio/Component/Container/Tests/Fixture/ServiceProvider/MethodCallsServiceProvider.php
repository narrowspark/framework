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

namespace Viserio\Component\Container\Tests\Fixture\ServiceProvider;

use Viserio\Contract\Container\Definition\Definition as DefinitionContract;
use Viserio\Contract\Container\ServiceProvider\ContainerBuilder as ContainerBuilderContract;
use Viserio\Contract\Container\ServiceProvider\ExtendServiceProvider as ExtendServiceProviderContract;
use Viserio\Contract\Container\ServiceProvider\ServiceProvider as ServiceProviderContract;

class MethodCallsServiceProvider implements ExtendServiceProviderContract, ServiceProviderContract
{
    /**
     * {@inheritdoc}
     */
    public function build(ContainerBuilderContract $container): void
    {
        $container->singleton('service', ServiceFixture::class)
            ->addMethodCall('addFoo', ['test'])
            ->setPublic(true);
    }

    /**
     * {@inheritdoc}
     */
    public function getExtensions(): array
    {
        return [
            'previous' => static function (DefinitionContract $definition): void {
                $definition->setValue($definition->getValue() . $definition->getValue());
            },
        ];
    }
}
