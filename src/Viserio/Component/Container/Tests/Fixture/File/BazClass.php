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

namespace Viserio\Component\Container\Tests\Fixture\File;

class BazClass
{
    protected $foo;

    public function setFoo(Foo $foo): void
    {
        $this->foo = $foo;
    }

    public static function getInstance(): BazClass
    {
        return new self();
    }
}
