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

use Viserio\Component\Container\Tests\Fixture\EmptyClass;

class VariadicClass
{
    public function __construct(EmptyClass $foo)
    {
    }

    public function bar(...$arguments): void
    {
    }
}
