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

use stdClass;
use Viserio\Component\Container\Definition\UndefinedDefinition;
use Viserio\Component\Container\Tests\Fixture\FactoryClass;
use Viserio\Component\Container\Tests\UnitTest\Definition\Traits\ArgumentsTestTrait;
use Viserio\Component\Container\Tests\UnitTest\Definition\Traits\ClassTestTrait;
use Viserio\Component\Container\Tests\UnitTest\Definition\Traits\DecoratedServiceTestTrait;
use Viserio\Component\Container\Tests\UnitTest\Definition\Traits\MethodCallsTestTrait;
use Viserio\Contract\Container\Definition\Definition as DefinitionContract;

/**
 * @internal
 *
 * @property UndefinedDefinition $definition
 *
 * @small
 */
final class UndefinedDefinitionTest extends AbstractDefinitionTest
{
    use ArgumentsTestTrait;
    use DecoratedServiceTestTrait;
    use ClassTestTrait;
    use MethodCallsTestTrait;

    public function testIsAutowired(): void
    {
        self::assertFalse($this->definition->isAutowired());

        $this->definition->setClass(stdClass::class);

        self::assertTrue($this->definition->isAutowired());
    }

    public function testGetClass(): void
    {
        $this->definition->setClass(stdClass::class);

        self::assertSame(stdClass::class, $this->definition->getClass());
    }

    public function testSetAndGetProperties(): void
    {
        $props = ['foo' => ['test', false]];

        $this->definition->setProperties($props);

        self::assertSame($props, $this->definition->getProperties());
    }

    /**
     * {@inheritdoc}
     */
    protected function getDefinition(): UndefinedDefinition
    {
        return new UndefinedDefinition($this->getDefinitionName(), $this->value, DefinitionContract::SINGLETON);
    }

    /**
     * {@inheritdoc}
     */
    protected function getValue(): array
    {
        return [new FactoryClass(), 'create'];
    }

    /**
     * {@inheritdoc}
     */
    protected function getDefinitionName(): string
    {
        return 'test';
    }
}
