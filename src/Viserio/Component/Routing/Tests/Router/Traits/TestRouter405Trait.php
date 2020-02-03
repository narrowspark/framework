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

namespace Viserio\Component\Routing\Tests\Router\Traits;

use Narrowspark\HttpStatus\Exception\MethodNotAllowedException;

trait TestRouter405Trait
{
    /**
     * @dataProvider provideRouter405Cases
     *
     * @param mixed $httpMethod
     * @param mixed $uri
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
