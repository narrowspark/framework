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
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\UriInterface;
use Viserio\Component\HttpFactory\ServerRequestFactory;
use Viserio\Component\HttpFactory\UriFactory;
use Viserio\Contract\Http\Exception\InvalidArgumentException;

/**
 * @internal
 *
 * @small
 */
final class ServerRequestFactoryTest extends TestCase
{
    /** @var \Viserio\Component\HttpFactory\ServerRequestFactory */
    private $factory;

    /** @var array */
    private static $globalServer = [];

    /** @var array */
    private static $globalGet = [];

    /** @var array */
    private static $globalPost = [];

    /** @var array */
    private static $globalFiles = [];

    /** @var array */
    private static $globalCookie = [];

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        self::$globalServer = $_SERVER;
        self::$globalPost = $_POST;
        self::$globalGet = $_GET;
        self::$globalFiles = $_FILES;
        self::$globalCookie = $_COOKIE;

        $this->factory = new ServerRequestFactory();
    }

    /**
     * {@inheritdoc}
     */
    protected function tearDown(): void
    {
        parent::tearDown();

        $_SERVER = self::$globalServer;
        $_GET = self::$globalGet;
        $_POST = self::$globalPost;
        $_FILES = self::$globalFiles;
        $_COOKIE = self::$globalCookie;
    }

    /**
     * @return array
     */
    public function dataMethods(): array
    {
        return [
            ['GET'],
            ['POST'],
            ['PUT'],
            ['DELETE'],
            ['OPTIONS'],
            ['HEAD'],
        ];
    }

    public function dataServer(): iterable
    {
        $data = [];

        foreach ($this->dataMethods() as $methodData) {
            $data[] = [
                [
                    'REQUEST_METHOD' => $methodData[0],
                    'REQUEST_URI' => '/test',
                    'QUERY_STRING' => 'foo=1&bar=true',
                    'HTTP_HOST' => 'example.org',
                ],
            ];
        }

        return $data;
    }

    /**
     * @dataProvider dataServer
     *
     * @param mixed $server
     */
    public function testCreateServerRequest($server): void
    {
        $method = $server['REQUEST_METHOD'];
        $uri = "http://{$server['HTTP_HOST']}{$server['REQUEST_URI']}?{$server['QUERY_STRING']}";

        $request = $this->factory->createServerRequest($method, $uri);

        $this->assertServerRequest($request, $method, $uri);
    }

    /**
     * @dataProvider dataServer
     *
     * @param array $server
     */
    public function testCreateServerRequestFromArray(array $server): void
    {
        $method = $server['REQUEST_METHOD'];
        $uri = "http://{$server['HTTP_HOST']}{$server['REQUEST_URI']}?{$server['QUERY_STRING']}";

        $request = $this->factory->createServerRequest($method, $uri, $server);

        $this->assertServerRequest($request, $method, $uri);
    }

    /**
     * @dataProvider dataServer
     *
     * @param mixed $server
     */
    public function testCreateServerRequestWithUriObject($server): void
    {
        $method = $server['REQUEST_METHOD'];
        $uri = "http://{$server['HTTP_HOST']}{$server['REQUEST_URI']}?{$server['QUERY_STRING']}";

        $request = $this->factory->createServerRequest($method, $this->createUri($uri));

        $this->assertServerRequest($request, $method, $uri);
    }

    /**
     * @backupGlobals enabled
     */
    public function testCreateServerRequestDoesNotReadServerSuperglobal(): void
    {
        $_SERVER = ['HTTP_X_FOO' => 'bar'];

        $server = [
            'REQUEST_METHOD' => 'PUT',
            'REQUEST_URI' => '/test',
            'QUERY_STRING' => 'super=0',
            'HTTP_HOST' => 'example.org',
        ];

        $request = $this->factory->createServerRequest('PUT', '/test', $server);

        $serverParams = $request->getServerParams();

        self::assertNotEquals($_SERVER, $serverParams);
        self::assertArrayNotHasKey('HTTP_X_FOO', $serverParams);
    }

    public function testCreateServerRequestDoesNotReadCookieSuperglobal(): void
    {
        $_COOKIE = ['foo' => 'bar'];

        $request = $this->factory->createServerRequest('POST', 'http://example.org/test');

        self::assertEmpty($request->getCookieParams());
    }

    public function testCreateServerRequestDoesNotReadGetSuperglobal(): void
    {
        $_GET = ['foo' => 'bar'];

        $request = $this->factory->createServerRequest('POST', 'http://example.org/test');

        self::assertEmpty($request->getQueryParams());
    }

    public function testCreateServerRequestDoesNotReadFilesSuperglobal(): void
    {
        $_FILES = [['name' => 'foobar.dat', 'type' => 'application/octet-stream', 'tmp_name' => '/tmp/php45sd3f', 'error' => \UPLOAD_ERR_OK, 'size' => 4]];

        $request = $this->factory->createServerRequest('POST', 'http://example.org/test');

        self::assertEmpty($request->getUploadedFiles());
    }

    public function testCreateServerRequestDoesNotReadPostSuperglobal(): void
    {
        $_POST = ['foo' => 'bar'];

        $request = $this->factory->createServerRequest('POST', 'http://example.org/test');

        self::assertEmpty($request->getParsedBody());
    }

    public function testCreateServerRequestWithEmptyMethod(): void
    {
        $serverRequest = $this->factory->createServerRequest('', '/', ['REQUEST_METHOD' => 'GET']);

        self::assertSame('GET', $serverRequest->getMethod());
    }

    public function testCreateServerRequestWithEmptyMethodAndRequestMethod(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Cannot determine HTTP method.');

        $this->factory->createServerRequest('', '/');
    }

    /**
     * {@inheritdoc}
     */
    protected function createUri($uri): UriInterface
    {
        $uriFactory = new UriFactory();

        return $uriFactory->createUri($uri);
    }

    protected function assertServerRequest($request, $method, $uri): void
    {
        self::assertInstanceOf(ServerRequestInterface::class, $request);
        self::assertSame($method, $request->getMethod());
        self::assertSame($uri, (string) $request->getUri());
    }
}
