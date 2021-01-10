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

namespace Viserio\Component\HttpFactory\Tests;

use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Viserio\Component\HttpFactory\ResponseFactory;

/**
 * @internal
 */
abstract class ResponseFactoryTest extends TestCase
{
    /** @var ResponseFactoryInterface */
    protected $factory;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        $this->factory = new ResponseFactory();
    }

    public static function provideCreateResponseCases(): iterable
    {
        return [
            [200],
            [301],
            [404],
            [500],
        ];
    }

    /**
     * @dataProvider provideCreateResponseCases
     */
    public function testCreateResponse($code): void
    {
        $response = $this->factory->createResponse($code);

        $this->assertResponse($response, $code);
    }

    protected function assertResponse($response, $code): void
    {
        self::assertInstanceOf(ResponseInterface::class, $response);
        self::assertSame($code, $response->getStatusCode());
    }
}
