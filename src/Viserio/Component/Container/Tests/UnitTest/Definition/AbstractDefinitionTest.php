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

use Narrowspark\TestingHelper\Phpunit\MockeryTestCase;
use Viserio\Component\Container\Argument\ConditionArgument;
use Viserio\Component\Container\Definition\ArrayDefinition;
use Viserio\Component\Container\Definition\ClosureDefinition;
use Viserio\Component\Container\Definition\FactoryDefinition;
use Viserio\Component\Container\Definition\IteratorDefinition;
use Viserio\Component\Container\Definition\ObjectDefinition;
use Viserio\Component\Container\Definition\ParameterDefinition;
use Viserio\Component\Container\Definition\UndefinedDefinition;
use Viserio\Component\Container\Tests\UnitTest\Definition\Traits\ChangesTestTrait;
use Viserio\Contract\Container\Exception\InvalidArgumentException;

/**
 * @internal
 */
abstract class AbstractDefinitionTest extends MockeryTestCase
{
    use ChangesTestTrait;

    /** @var ArrayDefinition|ClosureDefinition|FactoryDefinition|IteratorDefinition|ObjectDefinition|ParameterDefinition|UndefinedDefinition */
    protected $definition;

    /** @var mixed */
    protected $value;

    /** @var string */
    protected $name;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->value = $this->getValue();
        $this->name = $this->getDefinitionName();
        $this->definition = $this->getDefinition();
    }

    /**
     * @return ArrayDefinition|ClosureDefinition|FactoryDefinition|IteratorDefinition|ObjectDefinition|ParameterDefinition|UndefinedDefinition
     */
    abstract protected function getDefinition();

    /**
     * @return mixed
     */
    abstract protected function getValue();

    public function testGetName(): void
    {
        self::assertSame($this->name, $this->definition->getName());
    }

    public function testGetValue(): void
    {
        self::assertSame($this->value, $this->definition->getValue());
    }

    public function testIsShared(): void
    {
        self::assertTrue($this->definition->isShared(), '->isShared() returns true by default');
    }

    public function testIsInternal(): void
    {
        $this->definition->setInternal(true);

        self::assertTrue($this->definition->isInternal());

        $this->definition->setInternal(false);

        self::assertFalse($this->definition->isInternal());
    }

    public function testTags(): void
    {
        $this->definition->setTags(['foo' => []]);

        self::assertTrue($this->definition->hasTag('foo'));

        $this->definition->addTag('bar');

        self::assertTrue($this->definition->hasTag('bar'));

        $this->definition->clearTag('bar');

        self::assertFalse($this->definition->hasTag('bar'));

        $this->definition->clearTags();

        self::assertCount(0, $this->definition->getTags());
    }

    public function testCondition(): void
    {
        $this->definition->setConditions([new ConditionArgument(['"foo" === "foo"'], function (): void {
        })]);

        $this->definition->addCondition(new ConditionArgument(['isset(true)'], function (): void {
        }));

        self::assertCount(2, $this->definition->getConditions());
    }

    public function testAddTagThrowExceptionOnEmptyTagName(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('The tag name cant be a empty string.');

        $this->definition->addTag('');
    }

    public function testSetTagsThrowExceptionOnEmptyTagName(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('The tag name cant be a empty string.');

        $this->definition->setTags(['' => []]);
    }

    public function testIsLazy(): void
    {
        self::assertFalse($this->definition->isLazy(), '->isLazy() returns false by default');
        self::assertSame($this->definition, $this->definition->setLazy(true), '->setLazy() implements a fluent interface');
        self::assertTrue($this->definition->isLazy(), '->isLazy() returns true if the service is lazy.');
    }

    public function testCanSetPublic(): void
    {
        self::assertFalse($this->definition->isPublic(), '->isPublic() returns false by default');
        self::assertSame($this->definition, $this->definition->setPublic(true), '->setPublic() implements a fluent interface');
        self::assertTrue($this->definition->isPublic(), '->isPublic() returns true if the instance must be public.');
    }

    public function testHasADefaultDeprecationMessage(): void
    {
        $this->definition->setDeprecated();

        self::assertSame('The [test] service is deprecated. You should stop using it, as it will be removed in the future.', $this->definition->getDeprecationMessage());
        self::assertTrue($this->definition->isDeprecated());

        $this->definition->setDeprecated(true, '[%s]');

        self::assertSame('[test]', $this->definition->getDeprecationMessage());
    }

    public function testReturnsCorrectDeprecationMessage(): void
    {
        $this->definition->setDeprecated(true, 'The "%s" is deprecated.');

        self::assertEquals('The "test" is deprecated.', $this->definition->getDeprecationMessage());
    }

    public function testSetDeprecatedThrowException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('The deprecation template must contain the [%s] placeholder.');

        $this->definition->setDeprecated(false, $this->name);
    }

    public function testCanOverrideDeprecation(): void
    {
        $this->definition->setDeprecated();

        $initial = $this->definition->isDeprecated();

        $this->definition->setDeprecated(false);

        $final = $this->definition->isDeprecated();

        self::assertTrue($initial);
        self::assertFalse($final);
    }

    public function testSetIsSynthetic(): void
    {
        self::assertFalse($this->definition->isSynthetic(), '->isSynthetic() returns false by default');
        self::assertSame($this->definition, $this->definition->setSynthetic(true), '->setSynthetic() implements a fluent interface');
        self::assertTrue($this->definition->isSynthetic(), '->isSynthetic() returns true if the service is synthetic.');
    }

    abstract protected function getDefinitionName(): string;
}
