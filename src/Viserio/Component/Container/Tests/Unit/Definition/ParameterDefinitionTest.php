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

namespace Viserio\Component\Container\Tests\Unit\Definition;

use Mockery;
use Psr\Container\ContainerInterface;
use Viserio\Component\Container\Definition\ParameterDefinition;
use Viserio\Contract\Container\Exception\InvalidArgumentException;

/**
 * @internal
 *
 * @covers \Viserio\Component\Container\Definition\ParameterDefinition
 *
 * @small
 */
final class ParameterDefinitionTest extends AbstractDefinitionTest
{
    /** @var \Mockery\MockInterface|\Psr\Container\ContainerInterface */
    private $containerMock;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->containerMock = Mockery::mock(ContainerInterface::class);
    }

    public function testIsShared(): void
    {
        self::assertTrue($this->definition->isShared());
    }

    public function testHasADefaultDeprecationMessage(): void
    {
        $this->definition->setDeprecated();

        self::assertSame('The [test] parameter is deprecated. You should stop using it, as it will be removed in the future.', $this->definition->getDeprecationMessage());
        self::assertTrue($this->definition->isDeprecated());

        $this->definition->setDeprecated(true, '[%s]');

        self::assertSame('[test]', $this->definition->getDeprecationMessage());
    }

    public function testIsLazy(): void
    {
        self::assertFalse($this->definition->isLazy(), '->isLazy() returns false by default');
        self::assertSame($this->definition, $this->definition->setLazy(true), '->setLazy() implements a fluent interface');
        self::assertFalse($this->definition->isLazy(), '->isLazy() returns false because this definition cant be lazy.');
    }

    public function testCanSetPublic(): void
    {
        self::assertTrue($this->definition->isPublic(), '->isPublic() returns true by default');
        self::assertSame($this->definition, $this->definition->setPublic(true), '->setPublic() implements a fluent interface');
        self::assertTrue($this->definition->isPublic(), '->isPublic() returns false because this definition is only public.');
    }

    public function testThrowExceptionOnInvalidType(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('You can´t register a ParameterDefinition [test] with a not supported type [Viserio\Component\Container\Definition\ParameterDefinition], supported types are ["int", "integer", "float", "string", "bool", "boolean", "array", "null", "array"].');

        $this->definition->setValue(new ParameterDefinition('fio', 'test'));
    }

    public function testThrowExceptionOnInvalidTypeInArray(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('You can´t register a ParameterDefinition [test] with a not supported type [Viserio\Component\Container\Definition\ParameterDefinition], supported types are ["int", "integer", "float", "string", "bool", "boolean", "array", "null", "array"].');

        $this->definition->setValue(['fioo', new ParameterDefinition('fio', 'test')]);
    }

    /**
     * {@inheritdoc}
     */
    protected function getValue(): string
    {
        return 'this is a string';
    }

    /**
     * {@inheritdoc}
     */
    protected function getDefinitionName(): string
    {
        return 'test';
    }

    /**
     * {@inheritdoc}
     */
    protected function getDefinition(): ParameterDefinition
    {
        return new ParameterDefinition($this->getDefinitionName(), $this->value);
    }
}
