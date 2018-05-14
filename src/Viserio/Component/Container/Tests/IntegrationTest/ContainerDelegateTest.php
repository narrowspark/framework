<?php
declare(strict_types=1);
namespace Viserio\Component\Container\Tests\IntegrationTest;

use DI\Container as DIContainer;
use Viserio\Component\Container\ContainerBuilder;

/**
 * @internal
 */
final class ContainerDelegateTest extends BaseContainerTest
{
    /**
     * @param \Viserio\Component\Container\ContainerBuilder $builder
     *
     * @dataProvider provideContainer
     */
    public function testDelegateContainer(ContainerBuilder $builder): void
    {
        $delegate = new DIContainer();
        $delegate->set('instance', function () {
            return 'value';
        });

        $builder->delegate($delegate);
        $builder->instance('instance2', $builder->get('instance'));

        $container = $builder->build();

        static::assertSame('value', $container->get('instance2'));
        static::assertTrue($container->hasInDelegate('instance'));
        static::assertFalse($container->hasInDelegate('instance3'));
    }
}
