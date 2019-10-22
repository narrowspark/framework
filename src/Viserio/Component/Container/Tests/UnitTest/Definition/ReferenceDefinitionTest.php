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

namespace Viserio\Component\Container\Tests\UnitTest\Definition;

use PHPUnit\Framework\TestCase;
use Viserio\Component\Container\Definition\ReferenceDefinition;
use Viserio\Component\Container\Tests\UnitTest\Definition\Traits\ChangesTestTrait;
use Viserio\Contract\Container\Exception\InvalidArgumentException;

/**
 * @internal
 *
 * @small
 */
final class ReferenceDefinitionTest extends TestCase
{
    use ChangesTestTrait;

    /** @var \Viserio\Contract\Container\Definition\ReferenceDefinition */
    protected $definition;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->definition = new ReferenceDefinition('foo');
    }

    public function testGetName(): void
    {
        self::assertSame('foo', $this->definition->getName());
    }

    public function testSetAndGetType(): void
    {
        self::assertNull($this->definition->getType(), '->getType() null is returned on default');
        self::assertSame($this->definition, $this->definition->setType(ReferenceDefinition::class), '->setType() implements a fluent interface');
        self::assertSame(ReferenceDefinition::class, $this->definition->getType(), '->getType() returns the type');
    }

    public function testExceptionOnEmptyMethodCall(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Method name cannot be empty.');

        $this->definition->addMethodCall('');
    }

    public function testMethodCalls(): void
    {
        self::assertSame($this->definition, $this->definition->setMethodCalls([['foo', ['foo']]]), '->setMethodCalls() implements a fluent interface');
        self::assertEquals([['foo', ['foo'], false]], $this->definition->getMethodCalls(), '->getMethodCalls() returns the methods to call');

        self::assertTrue($this->definition->hasMethodCall('foo'), '->hasMethodCall() returns true if first argument is a method to call registered');
        self::assertFalse($this->definition->hasMethodCall('no_registered'), '->hasMethodCall() returns false if first argument is not a method to call registered');
        self::assertSame($this->definition, $this->definition->removeMethodCall('foo'), '->removeMethodCall() implements a fluent interface');

        self::assertSame($this->definition, $this->definition->addMethodCall('foobar', ['foobar'], true), '->addMethodCall() implements a fluent interface with third parameter');
        self::assertEquals([['foobar', ['foobar'], true]], $this->definition->getMethodCalls(), '->addMethodCall() adds a method to call');

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('A ReferenceDefinition must hold one and only one method call.');

        self::assertSame($this->definition, $this->definition->addMethodCall('bar', ['bar']), '->addMethodCall() implements a fluent interface');
    }
}
