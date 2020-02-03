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

use OutOfBoundsException;
use Viserio\Component\Container\Definition\ReferenceDefinition;

/**
 * @property \Viserio\Contract\Container\Definition\ArgumentAwareDefinition $definition
 */
trait ArgumentsTestTrait
{
    public function testSetAddAndGetParameters(): void
    {
        self::assertSame($this->definition, $this->definition->setArguments(['test' => new ReferenceDefinition('foo')]), '->setArguments() implements a fluent interface');
        self::assertSame($this->definition, $this->definition->addArgument('bar'), '->addArgument() implements a fluent interface');

        self::assertTrue($this->definition->getChange('arguments'));
        self::assertCount(2, $this->definition->getArguments());
        self::assertInstanceOf(ReferenceDefinition::class, $this->definition->getArgument('test'));
    }

    public function testSetAndGetArgument(): void
    {
        self::assertSame($this->definition, $this->definition->setArgument('test', $expected = new ReferenceDefinition('foo')), '->setArgument() implements a fluent interface');

        self::assertSame($expected, $this->definition->getArgument('test'));
        self::assertInstanceOf(ReferenceDefinition::class, $this->definition->getArgument('test'));
    }

    public function testGetParameterThrowException(): void
    {
        $this->expectException(OutOfBoundsException::class);
        $this->expectExceptionMessage('The parameter [0] doesn\'t exist.');

        $this->definition->getArgument(0);
    }
}
