<?php
declare(strict_types=1);
namespace Viserio\Component\Container\Tests\IntegrationTest;

use Mouf\Picotainer\Picotainer;
use stdClass;
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
    public function testAliasToDependencyInDelegateContainer(ContainerBuilder $builder): void
    {
        $delegate = new Picotainer([
            'instance' => function () {
                return 'this is a value';
            },
        ]);

        $builder->delegate($delegate);
        $builder->instance('instance2', $builder->get('instance'));

        $container = $builder->build();

        $this->assertSame('this is a value', $container->get('instance2'));
        $this->assertTrue($container->hasInDelegate('instance'));
        $this->assertFalse($container->hasInDelegate('instance3'));
    }

    /**
     * @param \Viserio\Component\Container\ContainerBuilder $builder
     *
     * @dataProvider provideContainer
     */
    public function testWithContainerCall(ContainerBuilder $builder): void
    {
        $value = new stdClass();

        $delegate = new Picotainer([
            'stdClass' => function () use ($value) {
                return $value;
            },
        ]);

        $builder->delegate($delegate);

        $container = $builder->build();

        $result = $container->call(function (stdClass $foo) {
            return $foo;
        });

        $this->assertSame($value, $result, 'The root container was not used for the type-hint');
    }
}
