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

/**
 * @property \Viserio\Contract\Container\Definition\ChangeAwareDefinition $definition
 */
trait ChangesTestTrait
{
    public function testChanges(): void
    {
        $this->definition->setChanges($expected = ['class' => true]);

        self::assertSame($expected, $this->definition->getChanges());

        $this->definition->setChange('method', true);

        self::assertSame(\array_merge($expected, ['method' => true]), $this->definition->getChanges());
        self::assertTrue($this->definition->getChange('class'));
        self::assertFalse($this->definition->getChange('no_changed'));
    }
}
