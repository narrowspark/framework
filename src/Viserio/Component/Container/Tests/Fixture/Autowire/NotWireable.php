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

    public function setOptionalNotAutowireable(NotARealClass $n = null): void
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
