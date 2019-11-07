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

namespace Viserio\Component\Routing\Tests;

use BadMethodCallException;
use PHPUnit\Framework\TestCase;
use Viserio\Component\Routing\Tests\Fixture\Controller;
use Viserio\Component\Routing\Tests\Fixture\FooMiddleware;

/**
 * @internal
 *
 * @small
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
