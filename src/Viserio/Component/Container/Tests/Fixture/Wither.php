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

namespace Viserio\Component\Container\Tests\Fixture;

class Wither
{
    public $foo;

    public function setEmptyClass(EmptyClass $foo): void
    {
    }

    /**
     * @return static
     */
    public function withEmptyClass1(EmptyClass $foo): self
    {
        return $this->withEmptyClass2($foo);
    }

    /**
     * @return static
     */
    public function withEmptyClass2(EmptyClass $foo): self
    {
        $new = clone $this;
        $new->foo = $foo;

        return $new;
    }
}
