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

use Narrowspark\HttpStatus\Exception\MethodNotAllowedException;

trait TestRouter405Trait
{
    /**
     * @dataProvider provideRouter405Cases
     */
    public function testRouter405($httpMethod, $uri): void
    {
        $this->expectException(MethodNotAllowedException::class);

        $this->definitions($this->router);

        $this->router->dispatch($this->serverRequestFactory->createServerRequest($httpMethod, $uri));
    }

    abstract public function expectException(string $exception): void;

    abstract public static function provideRouter405Cases(): iterable;
}
