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

class Decorated implements DecoratorInterface
{
    public function __construct(
        $quz = null,
        \NonExistent $nonExistent = null,
        DecoratorInterface $decorated = null,
        array $foo = []
    ) {
    }
}
