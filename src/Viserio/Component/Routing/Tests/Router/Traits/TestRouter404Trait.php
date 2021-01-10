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

namespace Viserio\Component\Routing\Tests\Router\Traits;

use Narrowspark\HttpStatus\Exception\NotFoundException;

trait TestRouter404Trait
{
    /**
     * @dataProvider provideRouter404Cases
     */
    public function testRouter404($httpMethod, $uri): void
    {
        $this->expectException(NotFoundException::class);

        $this->router->dispatch($this->serverRequestFactory->createServerRequest($httpMethod, $uri));
    }

    abstract public function expectException(string $exception): void;

    abstract public static function provideRouter404Cases(): iterable;
}
