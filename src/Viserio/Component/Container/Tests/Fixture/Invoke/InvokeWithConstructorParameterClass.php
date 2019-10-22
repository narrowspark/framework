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

namespace Viserio\Component\Container\Tests\Fixture\Invoke;

use Viserio\Component\Container\Tests\Fixture\EmptyClass;

class InvokeWithConstructorParameterClass
{
    public function __construct(EmptyClass $class)
    {
    }

    public function __invoke(): string
    {
        return 'hallo';
    }
}
