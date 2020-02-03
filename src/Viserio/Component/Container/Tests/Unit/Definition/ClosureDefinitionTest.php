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

use Closure;
use Viserio\Component\Container\Definition\ClosureDefinition;
use Viserio\Component\Container\Tests\Unit\Definition\Traits\ArgumentsTestTrait;
use Viserio\Component\Container\Tests\Unit\Definition\Traits\AutowireTestTrait;
use Viserio\Component\Container\Tests\Unit\Definition\Traits\DecoratedServiceTestTrait;
use Viserio\Contract\Container\Definition\Definition as DefinitionContract;

/**
 * @internal
 *
 * @covers \Viserio\Component\Container\Definition\ClosureDefinition
 *
 * @property ClosureDefinition $definition
 *
 * @small
 */
final class ClosureDefinitionTest extends AbstractDefinitionTest
{
    use ArgumentsTestTrait;
    use DecoratedServiceTestTrait;
    use AutowireTestTrait;

    public function testGetValue(): void
    {
        self::assertInstanceOf(Closure::class, $this->definition->getValue());
    }

    /**
     * {@inheritdoc}
     */
    protected function getValue(): callable
    {
        return function () {
            return 'test';
        };
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
    protected function getDefinition(): ClosureDefinition
    {
        return new ClosureDefinition($this->getDefinitionName(), $this->value, DefinitionContract::SINGLETON);
    }
}
