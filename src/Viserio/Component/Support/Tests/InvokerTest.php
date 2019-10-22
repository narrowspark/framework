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

namespace Viserio\Component\Support\Tests;

use Narrowspark\TestingHelper\ArrayContainer;
use PHPUnit\Framework\TestCase;
use Viserio\Component\Support\Invoker;

/**
 * @internal
 *
 * @small
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
