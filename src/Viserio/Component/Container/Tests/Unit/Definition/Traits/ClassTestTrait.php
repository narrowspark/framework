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
use Viserio\Contract\Container\Definition\FactoryDefinition;
use Viserio\Contract\Container\Definition\ObjectDefinition;

/**
 * @property FactoryDefinition|ObjectDefinition|UndefinedDefinition $definition
 */
trait ClassTestTrait
{
    public function testSetGetClass(): void
    {
        $this->definition->setClass('foo');

        self::assertTrue($this->definition->getChange('class'));
        self::assertEquals('foo', $this->definition->getClass(), '->getClass() returns the class name');
    }
}
