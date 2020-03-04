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

use NonExistent;

class Decorated implements DecoratorInterface
{
    public function __construct(
        $quz = null,
        ?NonExistent $nonExistent = null,
        ?DecoratorInterface $decorated = null,
        array $foo = []
    ) {
    }
}
