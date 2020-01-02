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

namespace Viserio\Component\Http\Tests\Response;

use PHPUnit\Framework\TestCase;
use Viserio\Component\Http\Response\EmptyResponse;

/**
 * @internal
 *
 * @small
 */
final class EmptyResponseTest extends TestCase
{
    public function testConstructor(): void
    {
        $response = new EmptyResponse([], 201);

        self::assertEquals('', (string) $response->getBody());
        self::assertEquals(201, $response->getStatusCode());
    }

    public function testConstructorWithHeader(): void
    {
        $response = new EmptyResponse(['x-empty' => ['true']]);

        self::assertEquals('', (string) $response->getBody());
        self::assertEquals(204, $response->getStatusCode());
        self::assertEquals('true', $response->getHeaderLine('x-empty'));
    }
}
