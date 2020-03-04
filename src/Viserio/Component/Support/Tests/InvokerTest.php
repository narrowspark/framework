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

namespace Viserio\Component\Support\Tests;

use Narrowspark\TestingHelper\ArrayContainer;
use PHPUnit\Framework\TestCase;
use Viserio\Component\Support\Invoker;

/**
 * @internal
 *
 * @small
 * @coversNothing
 */
final class InvokerTest extends TestCase
{
    public function testCall(): void
    {
        $invoker = (new Invoker())
            ->injectByTypeHint(true)
            ->injectByParameterName(true)
            ->setContainer(new ArrayContainer([]));

        $call = $invoker->call(static function ($name) {
            return 'Hello ' . $name;
        }, ['John']);

        self::assertEquals('Hello John', $call);
    }
}
