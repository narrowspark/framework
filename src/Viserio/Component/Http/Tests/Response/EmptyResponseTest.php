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

namespace Viserio\Component\Http\Tests\Response;

use PHPUnit\Framework\TestCase;
use Viserio\Component\Http\Response\EmptyResponse;

/**
 * @internal
 *
 * @small
 * @coversNothing
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
