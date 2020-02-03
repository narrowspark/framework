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

namespace Viserio\Component\Container\Tests\Unit\Definition;

use stdClass;
use Viserio\Component\Container\Definition\ObjectDefinition;
use Viserio\Component\Container\Tests\Unit\Definition\Traits\ArgumentsTestTrait;
use Viserio\Component\Container\Tests\Unit\Definition\Traits\AutowireTestTrait;
use Viserio\Component\Container\Tests\Unit\Definition\Traits\ClassTestTrait;
use Viserio\Component\Container\Tests\Unit\Definition\Traits\DecoratedServiceTestTrait;
use Viserio\Component\Container\Tests\Unit\Definition\Traits\MethodCallsTestTrait;
use Viserio\Contract\Container\Definition\Definition as DefinitionContract;

/**
 * @internal
 *
 * @covers \Viserio\Component\Container\Definition\ObjectDefinition
 *
 * @small
 */
final class ObjectDefinitionTest extends AbstractDefinitionTest
{
    use ArgumentsTestTrait;
    use DecoratedServiceTestTrait;
    use MethodCallsTestTrait;
    use ClassTestTrait;
    use AutowireTestTrait;

    /** @var \Viserio\Component\Container\Definition\ObjectDefinition */
    protected $definition;

    public function testGetValue(): void
    {
        self::assertInstanceOf(stdClass::class, $this->definition->getValue());
    }

    public function testGetClass(): void
    {
        self::assertSame(stdClass::class, $this->definition->getClass());
    }

    public function testSetAndGetProperties(): void
    {
        $props = ['foo' => ['test', false]];

        $this->definition->setProperties($props);

        self::assertSame($props, $this->definition->getProperties());
    }

    public function testObjectDefinitionWithStdClassToFillProperties(): void
    {
        $definition = new ObjectDefinition('stdClass', $object = (object) [
            'only dot' => '.',
            'concatenation as value' => '.\'\'.',
            'concatenation from the start value' => '\'\'.',
            '.' => 'dot as a key',
            '.\'\'.' => 'concatenation as a key',
            '\'\'.' => 'concatenation from the start key',
            'optimize concatenation' => 'string1{some_string}string2',
            'optimize concatenation with empty string' => 'string1{empty_value}string2',
            'optimize concatenation from the start' => '{empty_value}start',
            'optimize concatenation at the end' => 'end{empty_value}',
            'new line' => "string with \nnew line",
        ], 0);

        self::assertCount(\count((array) $object), $definition->getProperties());

        foreach ($definition->getProperties() as $property => $data) {
            self::assertSame($object->{$property}, $data[0]);
            self::assertFalse($data[1]);
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function getDefinition(): ObjectDefinition
    {
        return new ObjectDefinition($this->getDefinitionName(), $this->value, DefinitionContract::SINGLETON);
    }

    /**
     * {@inheritdoc}
     */
    protected function getValue(): stdClass
    {
        return new stdClass();
    }

    /**
     * {@inheritdoc}
     */
    protected function getDefinitionName(): string
    {
        return 'test';
    }
}
