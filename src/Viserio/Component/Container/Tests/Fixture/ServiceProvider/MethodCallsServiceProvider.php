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
