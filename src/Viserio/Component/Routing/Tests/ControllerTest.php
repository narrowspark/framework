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

namespace Viserio\Component\Routing\Tests;

use BadMethodCallException;
use PHPUnit\Framework\TestCase;
use Viserio\Component\Routing\Tests\Fixture\Controller;
use Viserio\Component\Routing\Tests\Fixture\FooMiddleware;

/**
 * @internal
 *
 * @small
 * @coversNothing
 */
final class ControllerTest extends TestCase
{
    public function testGatherMiddleware(): void
    {
        $controller = new Controller();

        self::assertSame([], $controller->gatherMiddleware());

        $controller->withMiddleware(FooMiddleware::class);

        self::assertSame([FooMiddleware::class => FooMiddleware::class], $controller->gatherMiddleware());
    }

    public function testGatherDisabledMiddleware(): void
    {
        $controller = new Controller();

        self::assertSame([], $controller->gatherDisabledMiddleware());

        $controller->withoutMiddleware(FooMiddleware::class);

        self::assertSame([FooMiddleware::class => true], $controller->gatherDisabledMiddleware());
    }

    public function testThrowsExceptionOnMissingMethods(): void
    {
        $this->expectException(BadMethodCallException::class);
        $this->expectExceptionMessage('Method [put] does not exist.');

        $controller = new Controller();
        $controller->put();
    }
}
