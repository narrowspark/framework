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

namespace Viserio\Bridge\Phpstan\Tests\Fixture;

use EmptyIterator;
use stdClass;
use Viserio\Component\Container\Definition\ReferenceDefinition;
use Viserio\Contract\Container\ServiceProvider\ContainerBuilder as ContainerBuilderContract;
use Viserio\Contract\Container\ServiceProvider\ServiceProvider as ServiceProviderContract;

class ServiceProvider implements ServiceProviderContract
{
    /**
     * {@inheritdoc}
     */
    public function build(ContainerBuilderContract $container): void
    {
        $service1 = $container->singleton(Foo::class);
        $service2 = $container->singleton('closure', function (): void {
        });
        $service3 = $container->singleton('factory1', [Foo::class, 'getFoo']);
        $service4 = $container->singleton('factory2', Foo::class . '::getStaticFoo');
        $service5 = $container->singleton('foo', new stdClass());
        $service6 = $container->singleton('array1', []);
        $service7 = $container->singleton('factory3', \date('now'));
        $service8 = $container->singleton('factory4', [new Foo(), 'getFoo']);
        $service9 = $container->singleton('factory5', ['Viserio\Bridge\Phpstan\Tests\Fixture\Foo', 'getFoo']);
        $service10 = $container->singleton('array2', ['getFoo']);
        $service11 = $container->singleton('iterator', (object) ['da']);
        $service12 = $container->singleton('factory6', [new ReferenceDefinition('foo'), 'getFoo']);
        $service13 = $container->singleton('iterator2', new EmptyIterator());
        $service14 = $container->singleton('undefined');
        $service15 = $container->singleton('foo', new Foo());

        die;
    }
}
