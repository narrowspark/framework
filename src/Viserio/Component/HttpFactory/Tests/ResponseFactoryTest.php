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

    public function provideCreateResponseCases(): iterable
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
     *
     * @param mixed $code
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
