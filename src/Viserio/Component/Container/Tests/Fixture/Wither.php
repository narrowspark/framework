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

namespace Viserio\Component\Container\Tests\Fixture;

class Wither
{
    public $foo;

    public function setEmptyClass(EmptyClass $foo): void
    {
    }

    /**
     * @param EmptyClass $foo
     *
     * @return static
     */
    public function withEmptyClass1(EmptyClass $foo): self
    {
        return $this->withEmptyClass2($foo);
    }

    /**
     * @param EmptyClass $foo
     *
     * @return static
     */
    public function withEmptyClass2(EmptyClass $foo): self
    {
        $new = clone $this;
        $new->foo = $foo;

        return $new;
    }
}
