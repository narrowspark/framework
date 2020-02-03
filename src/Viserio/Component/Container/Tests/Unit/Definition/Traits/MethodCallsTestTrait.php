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

namespace Viserio\Component\Container\Tests\Unit\Definition\Traits;

use Viserio\Component\Container\Definition\UndefinedDefinition;
use Viserio\Contract\Container\Definition\ClosureDefinition as ClosureDefinitionContract;
use Viserio\Contract\Container\Definition\FactoryDefinition as FactoryDefinitionContract;
use Viserio\Contract\Container\Definition\ObjectDefinition as ObjectDefinitionContract;
use Viserio\Contract\Container\Definition\ReferenceDefinition as ReferenceDefinitionContract;
use Viserio\Contract\Container\Exception\InvalidArgumentException;

/**
 * @property ClosureDefinitionContract|FactoryDefinitionContract|ObjectDefinitionContract|ReferenceDefinitionContract|UndefinedDefinition $definition
 */
trait MethodCallsTestTrait
{
    public function testExceptionOnEmptyMethodCall(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Method name cannot be empty.');

        $this->definition->addMethodCall('');
    }

    public function testMethodCalls(): void
    {
        self::assertSame($this->definition, $this->definition->setMethodCalls([['foo', ['foo']]]), '->setMethodCalls() implements a fluent interface');
        self::assertTrue($this->definition->getChange('method_calls'));
        self::assertEquals([['foo', ['foo'], false]], $this->definition->getMethodCalls(), '->getMethodCalls() returns the methods to call');
        self::assertSame($this->definition, $this->definition->addMethodCall('bar', ['bar']), '->addMethodCall() implements a fluent interface');
        self::assertEquals([['foo', ['foo'], false], ['bar', ['bar'], false]], $this->definition->getMethodCalls(), '->addMethodCall() adds a method to call');
        self::assertSame($this->definition, $this->definition->addMethodCall('foobar', ['foobar'], true), '->addMethodCall() implements a fluent interface with third parameter');
        self::assertEquals([['foo', ['foo'], false], ['bar', ['bar'], false], ['foobar', ['foobar'], true]], $this->definition->getMethodCalls(), '->addMethodCall() adds a method to call');
        self::assertTrue($this->definition->hasMethodCall('bar'), '->hasMethodCall() returns true if first argument is a method to call registered');
        self::assertFalse($this->definition->hasMethodCall('no_registered'), '->hasMethodCall() returns false if first argument is not a method to call registered');
        self::assertSame($this->definition, $this->definition->removeMethodCall('bar'), '->removeMethodCall() implements a fluent interface');
        self::assertTrue($this->definition->hasMethodCall('foobar'), '->hasMethodCall() returns true if first argument is a method to call registered');
        self::assertSame($this->definition, $this->definition->removeMethodCall('foobar'), '->removeMethodCall() implements a fluent interface');
        self::assertEquals([['foo', ['foo'], false]], $this->definition->getMethodCalls(), '->removeMethodCall() removes a method to call');
        self::assertSame($this->definition, $this->definition->setMethodCalls([['foobar', ['foobar'], true]]), '->setMethodCalls() implements a fluent interface with third parameter');
        self::assertEquals([['foobar', ['foobar'], true]], $this->definition->getMethodCalls(), '->addMethodCall() adds a method to call');
    }
}
