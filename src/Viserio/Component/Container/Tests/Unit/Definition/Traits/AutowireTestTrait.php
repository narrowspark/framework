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

namespace Viserio\Component\Container\Tests\Unit\Definition\Traits;

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
