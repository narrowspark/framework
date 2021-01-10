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
