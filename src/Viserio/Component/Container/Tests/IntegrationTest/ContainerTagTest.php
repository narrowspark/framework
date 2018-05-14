<?php
declare(strict_types=1);
namespace Viserio\Component\Container\Tests\IntegrationTest;

use Viserio\Component\Container\ContainerBuilder;
use Viserio\Component\Contract\Container\Exception\InvalidArgumentException;

/**
 * @internal
 */
final class ContainerTagTest extends BaseContainerTest
{
    /**
     * @param \Viserio\Component\Container\ContainerBuilder $builder
     *
     * @dataProvider provideContainer
     *
     * @return void
     */
    public function testTag(ContainerBuilder $builder): void
    {
        $builder->instance('adapterA', 'a');
        $builder->instance('adapterB', 'b');
        $builder->instance('adapterC', 'c');

        $builder->tag('test', ['adapterA', 'adapterB', 'adapterC']);

        $array = $builder->build()->getTagged('test');

        static::assertSame('a', $array[0]);
        static::assertSame('b', $array[1]);
        static::assertSame('c', $array[2]);
    }

    /**
     * @param \Viserio\Component\Container\ContainerBuilder $builder
     *
     * @dataProvider provideContainer
     *
     * @return void
     */
    public function testTagToThrowExceptionOnEmptyString(ContainerBuilder $builder): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('The tag name cant be a empty string.');

        $builder->build()->tag('', []);
    }
}
