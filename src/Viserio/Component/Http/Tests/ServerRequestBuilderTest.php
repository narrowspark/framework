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

namespace Viserio\Component\Http\Tests;

use Fig\Http\Message\RequestMethodInterface;
use PHPUnit\Framework\TestCase;
use Viserio\Component\Http\ServerRequestBuilder;
use Viserio\Component\Http\Uri;
use Viserio\Contract\Http\Exception\InvalidArgumentException;
use Viserio\Contract\Http\Exception\UnexpectedValueException;

/**
 * @internal
 *
 * @small
 */
final class ServerRequestBuilderTest extends TestCase
{
    private const NUMBER_OF_FILES = 11;

    /** @var array */
    public static $filenames = [];

    /** @var array */
    private static $globalServer = [];

    /** @var \Viserio\Component\Http\ServerRequestBuilder */
    private $serverRequestBuilder;

    /**
     * {@inheritdoc}
     */
    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();

        self::initFiles();
    }

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->serverRequestBuilder = new ServerRequestBuilder();
        self::$globalServer = $_SERVER;
    }

    /**
     * {@inheritdoc}
     */
    protected function tearDown(): void
    {
        parent::tearDown();

        $_SERVER = self::$globalServer;
    }

    /**
     * @return void
     */
    public static function initFiles(): void
    {
        if (\count(self::$filenames) !== 0) {
            return;
        }

        $tmpDir = \sys_get_temp_dir();

        for ($i = 0; $i < self::NUMBER_OF_FILES; $i++) {
            self::$filenames[] = $filename = $tmpDir . '/file_' . $i;

            \file_put_contents($filename, 'foo' . $i);
        }
    }

    public function provideGetUriFromGlobalsCases(): iterable
    {
        self::initFiles();

        $server = $this->arrangeGlobalServer();

        $noHost = $server;

        unset($noHost['HTTP_HOST']);

        return [
            'Normal request' => [
                'http://www.narrowspark.com/doc/framwork.php?id=10&user=foo',
                $server,
            ],
            'Secure request' => [
                'https://www.narrowspark.com/doc/framwork.php?id=10&user=foo',
                \array_merge($server, ['HTTPS' => 'on', 'SERVER_PORT' => 443]),
            ],
            'No HTTPS param' => [
                'http://www.narrowspark.com/doc/framwork.php?id=10&user=foo',
                $server,
            ],
            'HTTP_HOST missing' => [
                'http://127.0.0.1/doc/framwork.php?id=10&user=foo',
                $noHost,
            ],
            'No query String' => [
                'http://www.narrowspark.com/doc/framwork.php',
                \array_merge($server, ['REQUEST_URI' => '/doc/framwork.php', 'QUERY_STRING' => '']),
            ],
            'Different port' => [
                'http://www.narrowspark.com:8324/doc/framwork.php?id=10&user=foo',
                \array_merge($server, ['SERVER_PORT' => '8324']),
            ],
            'IPv6 local loopback address' => [
                'http://[::1]:8000/doc/framwork.php?id=10&user=foo',
                \array_merge($server, ['HTTP_HOST' => '[::1]:8000']),
            ],
        ];
    }

    /**
     * @dataProvider provideGetUriFromGlobalsCases
     *
     * @param mixed $expected
     * @param mixed $serverParams
     */
    public function testGetUriFromGlobals($expected, $serverParams): void
    {
        $serverRequest = $this->serverRequestBuilder->createFromArray($serverParams);

        self::assertEquals(Uri::createFromString($expected), $serverRequest->getUri());
    }

    public function testFromGlobals(): void
    {
        $_SERVER = [
            'PHP_SELF' => '/doc/framwork.php',
            'GATEWAY_INTERFACE' => 'CGI/1.1',
            'SERVER_ADDR' => '127.0.0.1',
            'SERVER_NAME' => 'www.narrowspark.com',
            'SERVER_SOFTWARE' => 'Apache/2.2.15 (Win32) JRun/4.0 PHP/7.0.7',
            'SERVER_PROTOCOL' => 'HTTP/1.0',
            'REQUEST_METHOD' => 'POST',
            'REQUEST_TIME' => 'Request start time: 1280149029',
            'QUERY_STRING' => 'id=10&user=foo',
            'DOCUMENT_ROOT' => '/path/to/your/server/root/',
            'HTTP_ACCEPT' => 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
            'HTTP_ACCEPT_CHARSET' => 'ISO-8859-1,utf-8;q=0.7,*;q=0.7',
            'HTTP_ACCEPT_ENCODING' => 'gzip,deflate',
            'HTTP_ACCEPT_LANGUAGE' => 'en-gb,en;q=0.5',
            'HTTP_CONNECTION' => 'keep-alive',
            'HTTP_HOST' => 'www.narrowspark.com',
            'HTTP_REFERER' => 'http://previous.url.com',
            'HTTP_USER_AGENT' => 'Mozilla/5.0 (Windows; U; Windows NT 6.0; en-GB; rv:1.9.2.6) Gecko/20100625 Firefox/3.6.6 ( .NET CLR 3.5.30729)',
            'HTTPS' => '1',
            'REMOTE_ADDR' => '193.60.168.69',
            'REMOTE_HOST' => 'Client server\'s host name',
            'REMOTE_PORT' => '5390',
            'SCRIPT_FILENAME' => '/path/to/this/script.php',
            'SERVER_ADMIN' => 'webmaster@narrowspark.com',
            'SERVER_PORT' => 80,
            'SERVER_SIGNATURE' => 'Version signature: 5.123',
            'SCRIPT_NAME' => '/doc/framwork.php',
            'REQUEST_URI' => '/doc/framwork.php?id=10&user=foo',
        ];

        $server = $this->serverRequestBuilder->createFromGlobals();

        self::assertEquals('POST', $server->getMethod());
        self::assertEquals(
            [
                'Accept' => [
                    'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
                ],
                'Accept-Charset' => [
                    'ISO-8859-1,utf-8;q=0.7,*;q=0.7',
                ],
                'Accept-Encoding' => [
                    'gzip,deflate',
                ],
                'Accept-Language' => [
                    'en-gb,en;q=0.5',
                ],
                'Connection' => [
                    'keep-alive',
                ],
                'Host' => [
                    'www.narrowspark.com',
                ],
                'Referer' => [
                    'http://previous.url.com',
                ],
                'User-Agent' => [
                    'Mozilla/5.0 (Windows; U; Windows NT 6.0; en-GB; rv:1.9.2.6) Gecko/20100625 Firefox/3.6.6 ( .NET CLR 3.5.30729)',
                ],
            ],
            $server->getHeaders()
        );

        self::assertEquals('', (string) $server->getBody());
        self::assertEquals('1.0', $server->getProtocolVersion());
        self::assertEquals(
            Uri::createFromString('https://www.narrowspark.com:80/doc/framwork.php?id=10&user=foo'),
            $server->getUri()
        );
    }

    public function testFromArrays(): void
    {
        $server = $this->arrangeGlobalServer();

        $server['HTTPS'] = '1';
        $server['SERVER_ADDR'] = 'Server IP: 217.112.82.20';

        $cookie = [
            'logged-in' => 'yes!',
        ];

        $post = [
            'name' => 'Pesho',
            'email' => 'pesho@example.com',
        ];

        $get = [
            'id' => 10,
            'user' => 'foo',
        ];

        $files = [
            'file' => [
                'name' => 'MyFile.txt',
                'type' => 'text/plain',
                'tmp_name' => self::$filenames[10],
                'error' => \UPLOAD_ERR_OK,
                'size' => 5,
            ],
        ];

        $server = $this->serverRequestBuilder->createFromArray($server, [], $cookie, $get, $post, $files, 'foobar');

        self::assertEquals('POST', $server->getMethod());
        self::assertEquals(['Host' => ['www.narrowspark.com:80']], $server->getHeaders());
        self::assertEquals('foobar', (string) $server->getBody());
        self::assertEquals('1.0', $server->getProtocolVersion());
        self::assertEquals($cookie, $server->getCookieParams());
        self::assertEquals($post, $server->getParsedBody());
        self::assertEquals($get, $server->getQueryParams());
        self::assertEquals(
            Uri::createFromString('https://www.narrowspark.com:80/doc/framwork.php?id=10&user=foo'),
            $server->getUri()
        );

        /** @var \Psr\Http\Message\UploadedFileInterface $file */
        $file = $server->getUploadedFiles()['file'];
        self::assertEquals(5, $file->getSize());
        self::assertEquals(\UPLOAD_ERR_OK, $file->getError());
        self::assertEquals('MyFile.txt', $file->getClientFilename());
        self::assertEquals('text/plain', $file->getClientMediaType());
        self::assertEquals(self::$filenames[10], $file->getStream()->getMetadata('uri'));
    }

    public function provideCreateFromGlobalsCases(): iterable
    {
        $data = [];
        $methods = [
            RequestMethodInterface::METHOD_HEAD,
            RequestMethodInterface::METHOD_GET,
            RequestMethodInterface::METHOD_POST,
            RequestMethodInterface::METHOD_PUT,
            RequestMethodInterface::METHOD_PATCH,
            RequestMethodInterface::METHOD_DELETE,
            RequestMethodInterface::METHOD_PURGE,
            RequestMethodInterface::METHOD_OPTIONS,
            RequestMethodInterface::METHOD_TRACE,
            RequestMethodInterface::METHOD_CONNECT,
        ];

        foreach ($methods as $method) {
            $data[] = [
                [
                    'REQUEST_METHOD' => $method,
                    'REQUEST_URI' => '/test?foo=1&bar=true',
                    'QUERY_STRING' => 'foo=1&bar=true',
                    'HTTP_HOST' => 'example.org',
                ],
            ];
        }

        $data[] = [
            [
                'REQUEST_URI' => '/test?foo=1&bar=true',
                'QUERY_STRING' => 'foo=1&bar=true',
                'HTTP_HOST' => 'example.org',
            ],
        ];

        return $data;
    }

    /**
     * @dataProvider provideCreateFromGlobalsCases
     *
     * @param mixed $server
     */
    public function testCreateFromGlobals($server): void
    {
        $_SERVER = $server;

        $method = $server['REQUEST_METHOD'] ?? RequestMethodInterface::METHOD_GET;
        $uri = "http://{$server['HTTP_HOST']}{$server['REQUEST_URI']}";

        $request = $this->serverRequestBuilder->createFromGlobals();

        self::assertSame($method, $request->getMethod());
        self::assertSame($uri, (string) $request->getUri());
    }

    public function testCreateFromArrayWithoutMethod(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Cannot determine HTTP method.');

        $this->serverRequestBuilder->createFromArray([
            'REQUEST_URI' => '/test?foo=1&bar=true',
            'QUERY_STRING' => 'foo=1&bar=true',
            'HTTP_HOST' => 'example.org',
        ]);
    }

    public function testCreateFromArrayWithUnrecognizedProtocolVersion(): void
    {
        $this->expectException(UnexpectedValueException::class);
        $this->expectExceptionMessage('Unrecognized protocol version [test].');

        $this->serverRequestBuilder->createFromArray([
            'REQUEST_URI' => '/test?foo=1&bar=true',
            'QUERY_STRING' => 'foo=1&bar=true',
            'HTTP_HOST' => 'example.org',
            'REQUEST_METHOD' => RequestMethodInterface::METHOD_GET,
            'SERVER_PROTOCOL' => 'test',
        ]);
    }

    public function testMarshalsVariablesPrefixedByApacheFromServerArray(): void
    {
        $_SERVER = [
            'REQUEST_URI' => '/test?foo=1&bar=true',
            'QUERY_STRING' => 'foo=1&bar=true',
            'HTTP_HOST' => 'example.org',
            'REQUEST_METHOD' => RequestMethodInterface::METHOD_GET,
            'HTTP_X_FOO_BAR' => 'nonprefixed',
            'REDIRECT_HTTP_AUTHORIZATION' => 'token',
            'REDIRECT_HTTP_X_FOO_BAR' => 'prefixed',
        ];

        $serverRequest = $this->serverRequestBuilder->createFromGlobals();

        self::assertEquals(
            [
                'Host' => ['example.org'],
                'Authorization' => ['token'],
                'X-Foo-Bar' => ['nonprefixed'],
            ],
            $serverRequest->getHeaders()
        );

        $_SERVER = [
            'REQUEST_URI' => '/test?foo=1&bar=true',
            'QUERY_STRING' => 'foo=1&bar=true',
            'HTTP_HOST' => 'example.org',
            'REQUEST_METHOD' => RequestMethodInterface::METHOD_GET,
            'HTTP_X_FOO_BAR' => 'nonprefixed',
            'PHP_AUTH_USER' => 'token',
        ];

        $serverRequest = $this->serverRequestBuilder->createFromGlobals();

        self::assertEquals(
            [
                'Host' => ['example.org'],
                'Authorization' => ['Basic ' . \base64_encode('token:')],
                'X-Foo-Bar' => ['nonprefixed'],
            ],
            $serverRequest->getHeaders()
        );

        $_SERVER = [
            'REQUEST_URI' => '/test?foo=1&bar=true',
            'QUERY_STRING' => 'foo=1&bar=true',
            'HTTP_HOST' => 'example.org',
            'REQUEST_METHOD' => RequestMethodInterface::METHOD_GET,
            'HTTP_X_FOO_BAR' => 'nonprefixed',
            'PHP_AUTH_DIGEST' => 'token',
        ];

        $serverRequest = $this->serverRequestBuilder->createFromGlobals();

        self::assertEquals(
            [
                'Host' => ['example.org'],
                'Authorization' => ['token'],
                'X-Foo-Bar' => ['nonprefixed'],
            ],
            $serverRequest->getHeaders()
        );
    }

    public function testMarshalsExpectedHeadersFromServerArray(): void
    {
        $_SERVER = [
            'REQUEST_URI' => '/test?foo=1&bar=true',
            'QUERY_STRING' => 'foo=1&bar=true',
            'HTTP_HOST' => 'example.org',
            'REQUEST_METHOD' => RequestMethodInterface::METHOD_GET,
            'PHP_AUTH_DIGEST' => 'token',
            'HTTP_COOKIE' => 'COOKIE',
            'HTTP_AUTHORIZATION' => 'token',
            'HTTP_CONTENT_TYPE' => 'application/json',
            'HTTP_ACCEPT' => 'application/json',
            'HTTP_X_FOO_BAR' => 'FOOBAR',
            'CONTENT_MD5' => 'CONTENT-MD5',
            'CONTENT_LENGTH' => 'UNSPECIFIED',
        ];

        $serverRequest = $this->serverRequestBuilder->createFromGlobals();

        self::assertEquals(
            [
                'Host' => ['example.org'],
                'Authorization' => ['token'],
                'X-Foo-Bar' => ['FOOBAR'],
                'Cookie' => ['COOKIE'],
                'Content-Type' => ['application/json'],
                'Accept' => ['application/json'],
                'Content-Md5' => ['CONTENT-MD5'],
                'Content-Length' => ['UNSPECIFIED'],
            ],
            $serverRequest->getHeaders()
        );
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testFromGlobalsUsesCookieSuperGlobalWhenCookieHeaderIsNotSet(): void
    {
        $_COOKIE = [
            'foo_bar' => 'bat',
        ];
        $_SERVER = [
            'HTTP_HOST' => 'www.narrowspark.com',
        ];

        $serverRequest = $this->serverRequestBuilder->createFromGlobals();

        self::assertSame(['foo_bar' => 'bat'], $serverRequest->getCookieParams());
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState
     */
    public function testCreateFromGlobalsShouldPreserveKeysWhenCreatedWithAZeroValue(): void
    {
        $_SERVER = [
            'HTTP_HOST' => 'www.narrowspark.com',
            'HTTP_ACCEPT' => '0',
            'CONTENT_LENGTH' => '0',
        ];

        $serverRequest = $this->serverRequestBuilder->createFromGlobals();

        self::assertSame('0', $serverRequest->getHeaderLine('Accept'), 'accept should return 0');
        self::assertSame('0', $serverRequest->getHeaderLine('content-length'), 'content length should return 0');
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState
     */
    public function testCreateFromGlobalsShouldNotPreserveKeysWhenCreatedWithAnEmptyValue(): void
    {
        $_SERVER = [
            'HTTP_HOST' => 'www.narrowspark.com',
            'HTTP_ACCEPT' => '',
            'CONTENT_LENGTH' => '',
        ];

        $serverRequest = $this->serverRequestBuilder->createFromGlobals();

        self::assertFalse($serverRequest->hasHeader('accept'));
        self::assertFalse($serverRequest->hasHeader('content-length'));
    }

    /**
     * @return array
     */
    private function arrangeGlobalServer(): array
    {
        return [
            'PHP_SELF' => '/doc/framwork.php',
            'GATEWAY_INTERFACE' => 'CGI/1.1',
            'SERVER_ADDR' => '127.0.0.1',
            'SERVER_NAME' => 'www.narrowspark.com',
            'SERVER_SOFTWARE' => 'Apache/2.2.15 (Win32) JRun/4.0 PHP/7.0.7',
            'SERVER_PROTOCOL' => 'HTTP/1.0',
            'REQUEST_METHOD' => 'POST',
            'REQUEST_TIME' => 'Request start time: 1280149029',
            'QUERY_STRING' => 'id=10&user=foo',
            'DOCUMENT_ROOT' => '/path/to/your/server/root/',
            'HTTP_ACCEPT' => 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
            'HTTP_ACCEPT_CHARSET' => 'ISO-8859-1,utf-8;q=0.7,*;q=0.7',
            'HTTP_ACCEPT_ENCODING' => 'gzip,deflate',
            'HTTP_ACCEPT_LANGUAGE' => 'en-gb,en;q=0.5',
            'HTTP_CONNECTION' => 'keep-alive',
            'HTTP_HOST' => 'www.narrowspark.com',
            'HTTP_REFERER' => 'http://previous.url.com',
            'HTTP_USER_AGENT' => 'Mozilla/5.0 (Windows; U; Windows NT 6.0; en-GB; rv:1.9.2.6) Gecko/20100625 Firefox/3.6.6 ( .NET CLR 3.5.30729)',
            'REMOTE_ADDR' => '193.60.100.69',
            'REMOTE_HOST' => 'Client server\'s host name',
            'REMOTE_PORT' => '5390',
            'SCRIPT_FILENAME' => '/path/to/this/script.php',
            'SERVER_ADMIN' => 'webmaster@narrowspark.com',
            'SERVER_PORT' => '80',
            'SERVER_SIGNATURE' => 'Version signature: 5.124',
            'SCRIPT_NAME' => '/doc/framwork.php',
            'REQUEST_URI' => '/doc/framwork.php?id=10&user=foo',
            'HTTP__1' => '-1',
            132 => '123',
        ];
    }
}
