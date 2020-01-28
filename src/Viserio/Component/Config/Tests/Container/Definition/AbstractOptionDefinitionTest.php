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

namespace Viserio\Component\OptionsResolver\Tests\Container\Definition;

use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
abstract class AbstractOptionDefinitionTest extends TestCase
{
    /**
     * @return object
     */
    abstract protected function getObject(): object;

    /**
     * @return string
     */
    abstract protected function getOptionClassName(): string;

    public function testGetClass(): void
    {
        $object = $this->getObject();

        self::assertSame($this->getOptionClassName(), $object->getClass());
    }
}
