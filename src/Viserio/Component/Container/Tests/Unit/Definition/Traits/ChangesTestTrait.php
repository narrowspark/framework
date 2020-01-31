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
