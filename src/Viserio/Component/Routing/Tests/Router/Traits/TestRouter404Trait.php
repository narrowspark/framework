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

use Narrowspark\HttpStatus\Exception\NotFoundException;

trait TestRouter404Trait
{
    /**
     * @dataProvider provideRouter404Cases
     *
     * @param mixed $httpMethod
     * @param mixed $uri
     */
    public function testRouter404($httpMethod, $uri): void
    {
        $this->expectException(NotFoundException::class);

        $this->router->dispatch($this->serverRequestFactory->createServerRequest($httpMethod, $uri));
    }

    abstract public function expectException(string $exception): void;

    abstract public static function provideRouter404Cases(): iterable;
}
