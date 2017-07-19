<?php
declare(strict_types=1);
namespace Viserio\Component\HttpFactory\Tests;

use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;
use ReflectionProperty;
use Viserio\Component\Http\Uri;
use Viserio\Component\HttpFactory\ServerRequestFactory;

class ServerRequestFactoryTest extends TestCase
{
    private $factory;

    public function setUp(): void
    {
        $this->factory = new ServerRequestFactory();
    }

    /**
     * @expectedException \UnexpectedValueException
     * @expectedExceptionMessage Unrecognized protocol version (HTTPa1.0)
     */
    public function testWithWrongServerProtocol(): void
    {
        $this->factory->createServerRequestFromArray(['SERVER_PROTOCOL' => 'HTTPa1.0', 'HTTP_HOST' => 'example.org']);
    }

    public function dataGetUriFromGlobals()
    {
        $server = [
            'PHP_SELF'             => '/doc/framwork.php',
            'GATEWAY_INTERFACE'    => 'CGI/1.1',
            'SERVER_ADDR'          => '127.0.0.1',
            'SERVER_NAME'          => 'www.narrowspark.com',
            'SERVER_SOFTWARE'      => 'Apache/2.2.15 (Win32) JRun/4.0 PHP/7.0.7',
            'SERVER_PROTOCOL'      => 'HTTP/1.0',
            'REQUEST_METHOD'       => 'POST',
            'REQUEST_TIME'         => 'Request start time: 1280149029',
            'QUERY_STRING'         => 'id=10&user=foo',
            'DOCUMENT_ROOT'        => '/path/to/your/server/root/',
            'HTTP_ACCEPT'          => 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
            'HTTP_ACCEPT_CHARSET'  => 'ISO-8859-1,utf-8;q=0.7,*;q=0.7',
            'HTTP_ACCEPT_ENCODING' => 'gzip,deflate',
            'HTTP_ACCEPT_LANGUAGE' => 'en-gb,en;q=0.5',
            'HTTP_CONNECTION'      => 'keep-alive',
            'HTTP_HOST'            => 'www.narrowspark.com',
            'HTTP_REFERER'         => 'http://previous.url.com',
            'HTTP_USER_AGENT'      => 'Mozilla/5.0 (Windows; U; Windows NT 6.0; en-GB; rv:1.9.2.6) Gecko/20100625 Firefox/3.6.6 ( .NET CLR 3.5.30729)',
            'REMOTE_ADDR'          => '193.60.168.69',
            'REMOTE_HOST'          => 'Client server\'s host name',
            'REMOTE_PORT'          => '5390',
            'SCRIPT_FILENAME'      => '/path/to/this/script.php',
            'SERVER_ADMIN'         => 'webmaster@narrowspark.com',
            'SERVER_PORT'          => 80,
            'SERVER_SIGNATURE'     => 'Version signature: 5.124',
            'SCRIPT_NAME'          => '/doc/framwork.php',
            'REQUEST_URI'          => '/doc/framwork.php?id=10&user=foo',
        ];

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
        ];
    }

    /**
     * @dataProvider dataGetUriFromGlobals
     *
     * @param mixed $expected
     * @param mixed $serverParams
     */
    public function testGetUriFromGlobals($expected, $serverParams): void
    {
        $serverRequest = $this->factory->createServerRequestFromArray($serverParams);

        self::assertEquals(Uri::createFromString($expected), $serverRequest->getUri());
    }

    public function testFromGlobals(): void
    {
        $_SERVER = [
            'PHP_SELF'             => '/doc/framwork.php',
            'GATEWAY_INTERFACE'    => 'CGI/1.1',
            'SERVER_ADDR'          => '127.0.0.1',
            'SERVER_NAME'          => 'www.narrowspark.com',
            'SERVER_SOFTWARE'      => 'Apache/2.2.15 (Win32) JRun/4.0 PHP/7.0.7',
            'SERVER_PROTOCOL'      => 'HTTP/1.0',
            'REQUEST_METHOD'       => 'POST',
            'REQUEST_TIME'         => 'Request start time: 1280149029',
            'QUERY_STRING'         => 'id=10&user=foo',
            'DOCUMENT_ROOT'        => '/path/to/your/server/root/',
            'HTTP_ACCEPT'          => 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
            'HTTP_ACCEPT_CHARSET'  => 'ISO-8859-1,utf-8;q=0.7,*;q=0.7',
            'HTTP_ACCEPT_ENCODING' => 'gzip,deflate',
            'HTTP_ACCEPT_LANGUAGE' => 'en-gb,en;q=0.5',
            'HTTP_CONNECTION'      => 'keep-alive',
            'HTTP_HOST'            => 'www.narrowspark.com',
            'HTTP_REFERER'         => 'http://previous.url.com',
            'HTTP_USER_AGENT'      => 'Mozilla/5.0 (Windows; U; Windows NT 6.0; en-GB; rv:1.9.2.6) Gecko/20100625 Firefox/3.6.6 ( .NET CLR 3.5.30729)',
            'HTTPS'                => '1',
            'REMOTE_ADDR'          => '193.60.168.69',
            'REMOTE_HOST'          => 'Client server\'s host name',
            'REMOTE_PORT'          => '5390',
            'SCRIPT_FILENAME'      => '/path/to/this/script.php',
            'SERVER_ADMIN'         => 'webmaster@narrowspark.com',
            'SERVER_PORT'          => 80,
            'SERVER_SIGNATURE'     => 'Version signature: 5.123',
            'SCRIPT_NAME'          => '/doc/framwork.php',
            'REQUEST_URI'          => '/doc/framwork.php?id=10&user=foo',
            'PHP_AUTH_USER'        => 'foo',
            'PHP_AUTH_PW'          => 'bar',
        ];

        $server = $this->factory->createServerRequestFromArray($_SERVER);

        self::assertEquals('POST', $server->getMethod());
        self::assertEquals(
            [
                'accept' => [
                    'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
                ],
                'accept-charset' => [
                    'ISO-8859-1,utf-8;q=0.7,*;q=0.7',
                ],
                'accept-encoding' => [
                    'gzip,deflate',
                ],
                'accept-language' => [
                    'en-gb,en;q=0.5',
                ],
                'connection' => [
                    'keep-alive',
                ],
                'host' => [
                    'www.narrowspark.com',
                ],
                'referer' => [
                    'http://previous.url.com',
                ],
                'user-agent' => [
                    'Mozilla/5.0 (Windows; U; Windows NT 6.0; en-GB; rv:1.9.2.6) Gecko/20100625 Firefox/3.6.6 ( .NET CLR 3.5.30729)',
                ],
                'php-auth-user' => [
                    'foo',
                ],
                'php-auth-pw' => [
                    'bar',
                ],
                'authorization' => [
                    'Basic Zm9vOmJhcg==',
                ],
            ],
            $server->getHeaders()
        );
        self::assertEquals('', (string) $server->getBody());
        self::assertEquals('1.0', $server->getProtocolVersion());
        self::assertEquals(
            Uri::createFromString('https://foo:bar@www.narrowspark.com:80/doc/framwork.php?id=10&user=foo'),
            $server->getUri()
        );
    }

    public function dataMethods()
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

    public function dataServer()
    {
        $data = [];

        foreach ($this->dataMethods() as $methodData) {
            $data[] = [
                [
                    'REQUEST_METHOD' => $methodData[0],
                    'REQUEST_URI'    => '/test?foo=1&bar=true',
                    'QUERY_STRING'   => 'foo=1&bar=true',
                    'HTTP_HOST'      => 'example.org',
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
    public function testCreateServerRequestFromArray($server): void
    {
        $method  = $server['REQUEST_METHOD'];
        $uri     = "http://{$server['HTTP_HOST']}{$server['REQUEST_URI']}";
        $request = $this->factory->createServerRequestFromArray($server);

        self::assertServerRequest($request, $method, $uri);
    }

    /**
     * @dataProvider dataServer
     *
     * @param mixed $server
     */
    public function testCreateServerRequestWithUriObject($server): void
    {
        $method  = $server['REQUEST_METHOD'];
        $uri     = "http://{$server['HTTP_HOST']}{$server['REQUEST_URI']}";
        $request = $this->factory->createServerRequest($method, Uri::createFromString($uri));

        self::assertServerRequest($request, $method, $uri);
    }

    /**
     * @backupGlobals enabled
     */
    public function testCreateServerRequestDoesNotReadServerSuperglobal(): void
    {
        $_SERVER      = ['HTTP_X_FOO' => 'bar'];
        $request      = $this->factory->createServerRequest('POST', 'http://example.org/test');
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
        $_GET    = ['foo' => 'bar'];
        $request = $this->factory->createServerRequest('POST', 'http://example.org/test');

        self::assertEmpty($request->getQueryParams());
    }

    public function testCreateServerRequestDoesNotReadFilesSuperglobal(): void
    {
        $_FILES  = [['name' => 'foobar.dat', 'type' => 'application/octet-stream', 'tmp_name' => '/tmp/php45sd3f', 'error' => UPLOAD_ERR_OK, 'size' => 4]];
        $request = $this->factory->createServerRequest('POST', 'http://example.org/test');

        self::assertEmpty($request->getUploadedFiles());
    }

    public function testCreateServerRequestDoesNotReadPostSuperglobal(): void
    {
        $_POST   = ['foo' => 'bar'];
        $request = $this->factory->createServerRequest('POST', 'http://example.org/test');

        self::assertEmpty($request->getParsedBody());
    }

    public function testReturnsServerValueUnchangedIfHttpAuthorizationHeaderIsPresent(): void
    {
        $server = [
            'HTTP_HOST'          => 'example.org',
            'HTTP_AUTHORIZATION' => 'token',
            'HTTP_X_Foo'         => 'bar',
        ];

        self::assertSame($server, $this->factory->createServerRequestFromArray($server)->getServerParams());
    }

    public function testMarshalsExpectedHeadersFromServerArray(): void
    {
        $server = [
            'HTTP_HOST'          => 'example.org',
            'HTTP_COOKIE'        => 'COOKIE',
            'HTTP_AUTHORIZATION' => 'token',
            'HTTP_CONTENT_TYPE'  => 'application/json',
            'HTTP_ACCEPT'        => 'application/json',
            'HTTP_X_FOO_BAR'     => 'FOOBAR',
            'CONTENT_MD5'        => 'CONTENT-MD5',
            'CONTENT_LENGTH'     => 'UNSPECIFIED',
        ];
        $expected = [
            'host'           => ['example.org'],
            'cookie'         => ['COOKIE'],
            'authorization'  => ['token'],
            'content-type'   => ['application/json'],
            'accept'         => ['application/json'],
            'x-foo-bar'      => ['FOOBAR'],
            'content-md5'    => ['CONTENT-MD5'],
            'content-length' => ['UNSPECIFIED'],
        ];

        self::assertSame($expected, $this->factory->createServerRequestFromArray($server)->getHeaders());
    }

    public function testHttpPasswordIsOptional(): void
    {
        self::assertEquals(
            [
                'host'          => ['example.org'],
                'authorization' => ['Basic ' . \base64_encode('foo:')],
                'php-auth-user' => ['foo'],
                'php-auth-pw'   => [''],
            ],
            $this->factory->createServerRequestFromArray(['PHP_AUTH_USER' => 'foo', 'HTTP_HOST' => 'example.org'])->getHeaders()
        );
    }

    public function testHttpBasicAuthWithPhpCgi(): void
    {
        self::assertEquals(
            [
                'host'          => ['example.org'],
                'authorization' => ['Basic ' . \base64_encode('foo:bar')],
                'php-auth-user' => ['foo'],
                'php-auth-pw'   => ['bar'],
            ],
            $this->factory->createServerRequestFromArray(['HTTP_AUTHORIZATION' => 'Basic ' . \base64_encode('foo:bar'), 'HTTP_HOST' => 'example.org'])->getHeaders()
        );
    }

    public function testHttpBasicAuthWithPhpCgiBogus(): void
    {
        // Username and passwords should not be set as the header is bogus
        $headers = $this->factory->createServerRequestFromArray(['HTTP_AUTHORIZATION' => 'Basic_' . \base64_encode('foo:bar'), 'HTTP_HOST' => 'example.org'])->getHeaders();

        self::assertFalse(isset($headers['php-auth-user']));
        self::assertFalse(isset($headers['php-auth-pw']));
    }

    public function testHttpBasicAuthWithPhpCgiRedirect(): void
    {
        self::assertEquals(
            [
                'host'          => ['example.org'],
                'authorization' => ['Basic ' . \base64_encode('username:pass:word')],
                'php-auth-user' => ['username'],
                'php-auth-pw'   => ['pass:word'],
            ],
            $this->factory->createServerRequestFromArray(['REDIRECT_HTTP_AUTHORIZATION' => 'Basic ' . \base64_encode('username:pass:word'), 'HTTP_HOST' => 'example.org'])->getHeaders()
        );
    }

    public function testHttpBasicAuthWithPhpCgiEmptyPassword(): void
    {
        self::assertEquals(
            [
                'host'          => ['example.org'],
                'authorization' => ['Basic ' . \base64_encode('foo:')],
                'php-auth-user' => ['foo'],
                'php-auth-pw'   => [''],
            ],
            $this->factory->createServerRequestFromArray(['HTTP_AUTHORIZATION' => 'Basic ' . \base64_encode('foo:'), 'HTTP_HOST' => 'example.org'])->getHeaders()
        );
    }

    public function testHttpDigestAuthWithPhpCgi(): void
    {
        $digest = 'Digest username="foo", realm="acme", nonce="' . \md5('secret') . '", uri="/protected, qop="auth"';

        self::assertEquals(
            [
                'host'            => ['example.org'],
                'authorization'   => [$digest],
                'php-auth-digest' => [$digest],
            ],
            $this->factory->createServerRequestFromArray(['HTTP_AUTHORIZATION' => $digest, 'HTTP_HOST' => 'example.org'])->getHeaders()
        );
    }

    public function testHttpDigestAuthWithPhpCgiBogus(): void
    {
        $digest = 'Digest_username="foo", realm="acme", nonce="' . \md5('secret') . '", uri="/protected, qop="auth"';

        // Username and passwords should not be set as the header is bogus
        $headers = $this->factory->createServerRequestFromArray(['HTTP_AUTHORIZATION' => $digest, 'HTTP_HOST' => 'example.org'])->getHeaders();

        self::assertFalse(isset($headers['php-auth-user']));
        self::assertFalse(isset($headers['php-auth-pw']));
    }

    public function testHttpDigestAuthWithPhpCgiRedirect(): void
    {
        $digest = 'Digest username="foo", realm="acme", nonce="' . \md5('secret') . '", uri="/protected, qop="auth"';
        self::assertEquals(
            [
                'host'            => ['example.org'],
                'authorization'   => [$digest],
                'php-auth-digest' => [$digest],
            ],
            $this->factory->createServerRequestFromArray(['REDIRECT_HTTP_AUTHORIZATION' => $digest, 'HTTP_HOST' => 'example.org'])->getHeaders()
        );
    }

    public function testOAuthBearerAuth(): void
    {
        $headerContent = 'Bearer L-yLEOr9zhmUYRkzN1jwwxwQ-PBNiKDc8dgfB4hTfvo';

        self::assertEquals(
            [
                'host'          => ['example.org'],
                'authorization' => [$headerContent],
            ],
            $this->factory->createServerRequestFromArray(['HTTP_AUTHORIZATION' => $headerContent, 'HTTP_HOST' => 'example.org'])->getHeaders()
        );
    }

    public function testOAuthBearerAuthWithRedirect(): void
    {
        $headerContent = 'Bearer L-yLEOr9zhmUYRkzN1jwwxwQ-PBNiKDc8dgfB4hTfvo';

        self::assertEquals(
            [
                'host'          => ['example.org'],
                'authorization' => [$headerContent],
            ],
            $this->factory->createServerRequestFromArray(['REDIRECT_HTTP_AUTHORIZATION' => $headerContent, 'HTTP_HOST' => 'example.org'])->getHeaders()
        );
    }

    /**
     * @see https://github.com/symfony/symfony/issues/17345
     */
    public function testItDoesNotOverwriteTheAuthorizationHeaderIfItIsAlreadySet(): void
    {
        $headerContent = 'Bearer L-yLEOr9zhmUYRkzN1jwwxwQ-PBNiKDc8dgfB4hTfvo';

        self::assertEquals(
            [
                'host'          => ['example.org'],
                'authorization' => [$headerContent],
                'php-auth-user' => ['foo'],
                'php-auth-pw'   => [''],
            ],
            $this->factory->createServerRequestFromArray(['PHP_AUTH_USER' => 'foo', 'HTTP_AUTHORIZATION' => $headerContent, 'HTTP_HOST' => 'example.org'])->getHeaders()
        );
    }

    public function testNormalizeServerUsesMixedCaseAuthorizationHeaderFromApacheWhenPresent(): void
    {
        $r = new ReflectionProperty(ServerRequestFactory::class, 'apacheRequestHeaders');
        $r->setAccessible(true);
        $r->setValue(function () {
            return ['Authorization' => 'foobar'];
        });

        $headers = $this->factory->createServerRequestFromArray(['HTTP_HOST' => 'example.org'])->getHeaders();

        self::assertArrayHasKey('authorization', $headers);
        self::assertEquals(['foobar'], $headers['authorization']);
    }

    public function testNormalizeServerUsesLowerCaseAuthorizationHeaderFromApacheWhenPresent(): void
    {
        $r = new ReflectionProperty(ServerRequestFactory::class, 'apacheRequestHeaders');
        $r->setAccessible(true);
        $r->setValue(function () {
            return ['authorization' => 'foobar'];
        });

        $headers = $this->factory->createServerRequestFromArray(['HTTP_HOST' => 'example.org'])->getHeaders();

        self::assertArrayHasKey('authorization', $headers);
        self::assertEquals(['foobar'], $headers['authorization']);
    }

    public function testNormalizeServerReturnsArrayUnalteredIfApacheHeadersDoNotContainAuthorization(): void
    {
        $r = new ReflectionProperty(ServerRequestFactory::class, 'apacheRequestHeaders');
        $r->setAccessible(true);
        $r->setValue(function () {
            return [];
        });

        $headers = $this->factory->createServerRequestFromArray(['HTTP_HOST' => 'example.org'])->getHeaders();

        self::assertEquals(['host' => ['example.org']], $headers);
    }

    protected function assertServerRequest($request, $method, $uri): void
    {
        self::assertInstanceOf(ServerRequestInterface::class, $request);
        self::assertSame($method, $request->getMethod());
        self::assertSame($uri, (string) $request->getUri());
    }
}
