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

namespace Viserio\Component\Container\Tests\Fixture\Autowire;

class NotWireable
{
    public function setNotAutowireable(NotARealClass $n): void
    {
    }

    public function setNotAutowireableBecauseOfATypo(lesTilleuls $sam): void
    {
    }

    public function setBar(): void
    {
    }

    public function setOptionalNotAutowireable(?NotARealClass $n = null): void
    {
    }

    public function setDifferentNamespace(\Foo\stdClass $n): void
    {
    }

    public function setOptionalNoTypeHint($foo = null): void
    {
    }

    public function setOptionalArgNoAutowireable($other = 'default_val'): void
    {
    }

    protected function setProtectedMethod(A $a): void
    {
    }
}
