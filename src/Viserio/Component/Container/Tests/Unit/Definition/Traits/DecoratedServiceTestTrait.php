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
use Viserio\Contract\Container\Definition\ClosureDefinition;
use Viserio\Contract\Container\Definition\FactoryDefinition;
use Viserio\Contract\Container\Definition\ObjectDefinition;
use Viserio\Contract\Container\Definition\ReferenceDefinition as ReferenceDefinitionContract;

/**
 * @property ClosureDefinition|FactoryDefinition|ObjectDefinition|UndefinedDefinition $definition
 */
trait DecoratedServiceTestTrait
{
    public function testSetGetDecoratedService(): void
    {
        self::assertNull($this->definition->getDecorator());

        $this->definition->decorate('foo', 'foo.renamed', 5);

        self::assertEquals(['foo', 'foo.renamed', 5,  null], $this->definition->getDecorator());

        $this->definition->removeDecorator();

        self::assertNull($this->definition->getDecorator());

        self::assertNull($this->definition->getDecorator());

        $this->definition->decorate('foo', 'foo.renamed');

        self::assertEquals(['foo', 'foo.renamed', 0,  null], $this->definition->getDecorator());

        $this->definition->removeDecorator();

        self::assertNull($this->definition->getDecorator());

        $this->definition->decorate('foo');

        self::assertEquals(['foo', null, 0,  null], $this->definition->getDecorator());

        $this->definition->removeDecorator();

        self::assertNull($this->definition->getDecorator());

        $this->definition->decorate('foo', 'foo.renamed', 5, ReferenceDefinitionContract::NULL_ON_INVALID_REFERENCE);
        $this->assertEquals(['foo', 'foo.renamed', 5, ReferenceDefinitionContract::NULL_ON_INVALID_REFERENCE], $this->definition->getDecorator());

        $this->definition->removeDecorator();

        $this->assertNull($this->definition->getDecorator());

        $this->expectException('InvalidArgumentException');
        $this->expectExceptionMessage('The decorated service inner name for [foo] must be different than the service name itself.');

        $this->definition->decorate('foo', 'foo');
    }
}
