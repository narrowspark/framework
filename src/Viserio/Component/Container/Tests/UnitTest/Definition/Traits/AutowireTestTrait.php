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

namespace Viserio\Component\Container\Tests\UnitTest\Definition\Traits;

use Viserio\Component\Container\Definition\ReferenceDefinition;
use Viserio\Component\Container\Definition\UndefinedDefinition;
use Viserio\Contract\Container\Definition\ClosureDefinition;
use Viserio\Contract\Container\Definition\FactoryDefinition;
use Viserio\Contract\Container\Definition\ObjectDefinition;

/**
 * @property ClosureDefinition|FactoryDefinition|ObjectDefinition|ReferenceDefinition|UndefinedDefinition $definition
 */
trait AutowireTestTrait
{
    public function testIsAutowired(): void
    {
        self::assertTrue($this->definition->isAutowired());

        $this->definition->setAutowired(false);

        self::assertTrue($this->definition->getChange('autowired'));
        self::assertFalse($this->definition->isAutowired());
    }
}
