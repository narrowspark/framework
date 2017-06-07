<?php
declare(strict_types=1);
namespace Viserio\Component\Support\Tests\Http;

use Narrowspark\TestingHelper\Phpunit\MockeryTestCase;
use Psr\Http\Message\ServerRequestInterface;
use GuzzleHttp\Psr7\UploadedFile;
use Viserio\Component\Support\Http\GlobalServerRequest;

class GlobalServerRequestTest extends MockeryTestCase
{
    private $factory;

    public function setUp()
    {
        $this->factory = new GlobalServerRequest();
    }

    /**
     * @expectedException \UnexpectedValueException
     * @expectedExceptionMessage Unrecognized protocol version (HTTPa1.0)
     */
    public function testWithWrongServerProtocol()
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
                array_merge($server, ['HTTPS' => 'on', 'SERVER_PORT' => 443]),
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
                array_merge($server, ['REQUEST_URI' => '/doc/framwork.php', 'QUERY_STRING' => '']),
            ],
        ];
    }

    /**
     * @dataProvider dataGetUriFromGlobals
     *
     * @param mixed $expected
     * @param mixed $serverParams
     */
    public function testGetUriFromGlobals($expected, $serverParams)
    {
        $serverRequest = $this->factory->createServerRequestFromArray($serverParams);

        self::assertEquals(Uri::createFromString($expected), $serverRequest->getUri());
    }

    public function testFromGlobals()
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
        self::assertEquals([
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
    public function testCreateServerRequestFromArray($server)
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
    public function testCreateServerRequestWithUriObject($server)
    {
        $method  = $server['REQUEST_METHOD'];
        $uri     = "http://{$server['HTTP_HOST']}{$server['REQUEST_URI']}";
        $request = $this->factory->createServerRequest($method, Uri::createFromString($uri));

        self::assertServerRequest($request, $method, $uri);
    }

    /**
     * @backupGlobals enabled
     */
    public function testCreateServerRequestDoesNotReadServerSuperglobal()
    {
        $_SERVER      = ['HTTP_X_FOO' => 'bar'];
        $request      = $this->factory->createServerRequest('POST', 'http://example.org/test');
        $serverParams = $request->getServerParams();

        self::assertNotEquals($_SERVER, $serverParams);
        self::assertArrayNotHasKey('HTTP_X_FOO', $serverParams);
    }

    public function testCreateServerRequestDoesNotReadCookieSuperglobal()
    {
        $_COOKIE = ['foo' => 'bar'];
        $request = $this->factory->createServerRequest('POST', 'http://example.org/test');

        self::assertEmpty($request->getCookieParams());
    }

    public function testCreateServerRequestDoesNotReadGetSuperglobal()
    {
        $_GET    = ['foo' => 'bar'];
        $request = $this->factory->createServerRequest('POST', 'http://example.org/test');

        self::assertEmpty($request->getQueryParams());
    }

    public function testCreateServerRequestDoesNotReadFilesSuperglobal()
    {
        $_FILES  = [['name' => 'foobar.dat', 'type' => 'application/octet-stream', 'tmp_name' => '/tmp/php45sd3f', 'error' => UPLOAD_ERR_OK, 'size' => 4]];
        $request = $this->factory->createServerRequest('POST', 'http://example.org/test');

        self::assertEmpty($request->getUploadedFiles());
    }

    public function testCreateServerRequestDoesNotReadPostSuperglobal()
    {
        $_POST   = ['foo' => 'bar'];
        $request = $this->factory->createServerRequest('POST', 'http://example.org/test');

        self::assertEmpty($request->getParsedBody());
    }

    public function testReturnsServerValueUnchangedIfHttpAuthorizationHeaderIsPresent()
    {
        $server = [
            'HTTP_HOST'          => 'example.org',
            'HTTP_AUTHORIZATION' => 'token',
            'HTTP_X_Foo'         => 'bar',
        ];

        self::assertSame($server, $this->factory->createServerRequestFromArray($server)->getServerParams());
    }

    public function testMarshalsExpectedHeadersFromServerArray()
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

    public function testHttpPasswordIsOptional()
    {
        self::assertEquals(
            [
                'host'          => ['example.org'],
                'authorization' => ['Basic ' . base64_encode('foo:')],
                'php-auth-user' => ['foo'],
                'php-auth-pw'   => [''],
            ],
            $this->factory->createServerRequestFromArray(['PHP_AUTH_USER' => 'foo', 'HTTP_HOST' => 'example.org'])->getHeaders()
        );
    }

    public function testHttpBasicAuthWithPhpCgi()
    {
        self::assertEquals(
            [
                'host'          => ['example.org'],
                'authorization' => ['Basic ' . base64_encode('foo:bar')],
                'php-auth-user' => ['foo'],
                'php-auth-pw'   => ['bar'],
            ],
            $this->factory->createServerRequestFromArray(['HTTP_AUTHORIZATION' => 'Basic ' . base64_encode('foo:bar'), 'HTTP_HOST' => 'example.org'])->getHeaders()
        );
    }

    public function testHttpBasicAuthWithPhpCgiBogus()
    {
        // Username and passwords should not be set as the header is bogus
        $headers = $this->factory->createServerRequestFromArray(['HTTP_AUTHORIZATION' => 'Basic_' . base64_encode('foo:bar'), 'HTTP_HOST' => 'example.org'])->getHeaders();

        self::assertFalse(isset($headers['php-auth-user']));
        self::assertFalse(isset($headers['php-auth-pw']));
    }

    public function testHttpBasicAuthWithPhpCgiRedirect()
    {
        self::assertEquals(
            [
                'host'          => ['example.org'],
                'authorization' => ['Basic ' . base64_encode('username:pass:word')],
                'php-auth-user' => ['username'],
                'php-auth-pw'   => ['pass:word'],
            ],
            $this->factory->createServerRequestFromArray(['REDIRECT_HTTP_AUTHORIZATION' => 'Basic ' . base64_encode('username:pass:word'), 'HTTP_HOST' => 'example.org'])->getHeaders()
        );
    }

    public function testHttpBasicAuthWithPhpCgiEmptyPassword()
    {
        self::assertEquals(
            [
                'host'          => ['example.org'],
                'authorization' => ['Basic ' . base64_encode('foo:')],
                'php-auth-user' => ['foo'],
                'php-auth-pw'   => [''],
            ],
            $this->factory->createServerRequestFromArray(['HTTP_AUTHORIZATION' => 'Basic ' . base64_encode('foo:'), 'HTTP_HOST' => 'example.org'])->getHeaders()
        );
    }

    public function testHttpDigestAuthWithPhpCgi()
    {
        $digest = 'Digest username="foo", realm="acme", nonce="' . md5('secret') . '", uri="/protected, qop="auth"';

        self::assertEquals(
            [
                'host'            => ['example.org'],
                'authorization'   => [$digest],
                'php-auth-digest' => [$digest],
            ],
            $this->factory->createServerRequestFromArray(['HTTP_AUTHORIZATION' => $digest, 'HTTP_HOST' => 'example.org'])->getHeaders()
        );
    }

    public function testHttpDigestAuthWithPhpCgiBogus()
    {
        $digest = 'Digest_username="foo", realm="acme", nonce="' . md5('secret') . '", uri="/protected, qop="auth"';

        // Username and passwords should not be set as the header is bogus
        $headers = $this->factory->createServerRequestFromArray(['HTTP_AUTHORIZATION' => $digest, 'HTTP_HOST' => 'example.org'])->getHeaders();

        self::assertFalse(isset($headers['php-auth-user']));
        self::assertFalse(isset($headers['php-auth-pw']));
    }

    public function testHttpDigestAuthWithPhpCgiRedirect()
    {
        $digest = 'Digest username="foo", realm="acme", nonce="' . md5('secret') . '", uri="/protected, qop="auth"';
        self::assertEquals(
            [
                'host'            => ['example.org'],
                'authorization'   => [$digest],
                'php-auth-digest' => [$digest],
            ],
            $this->factory->createServerRequestFromArray(['REDIRECT_HTTP_AUTHORIZATION' => $digest, 'HTTP_HOST' => 'example.org'])->getHeaders()
        );
    }

    public function testOAuthBearerAuth()
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

    public function testOAuthBearerAuthWithRedirect()
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
    public function testItDoesNotOverwriteTheAuthorizationHeaderIfItIsAlreadySet()
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

    public function testNormalizeServerUsesMixedCaseAuthorizationHeaderFromApacheWhenPresent()
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

    public function testNormalizeServerUsesLowerCaseAuthorizationHeaderFromApacheWhenPresent()
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

    public function testNormalizeServerReturnsArrayUnalteredIfApacheHeadersDoNotContainAuthorization()
    {
        $r = new ReflectionProperty(ServerRequestFactory::class, 'apacheRequestHeaders');
        $r->setAccessible(true);
        $r->setValue(function () {
            return [];
        });

        $headers = $this->factory->createServerRequestFromArray(['HTTP_HOST' => 'example.org'])->getHeaders();

        self::assertEquals(['host' => ['example.org']], $headers);
    }

    protected function assertServerRequest($request, $method, $uri)
    {
        self::assertInstanceOf(ServerRequestInterface::class, $request);
        self::assertSame($method, $request->getMethod());
        self::assertSame($uri, (string) $request->getUri());
    }

    public function dataNormalizeFiles()
    {
        return [
            'Single file' => [
                [
                    'file' => [
                        'name'     => 'MyFile.txt',
                        'type'     => 'text/plain',
                        'tmp_name' => '/tmp/php/php1h4j1o',
                        'error'    => '0',
                        'size'     => '123',
                    ],
                ],
                [
                    'file' => new UploadedFile(
                        '/tmp/php/php1h4j1o',
                        123,
                        UPLOAD_ERR_OK,
                        'MyFile.txt',
                        'text/plain'
                    ),
                ],
            ],
            'Empty file' => [
                [
                    'image_file' => [
                        'name'     => '',
                        'type'     => '',
                        'tmp_name' => '',
                        'error'    => '4',
                        'size'     => '0',
                    ],
                ],
                [
                    'image_file' => new UploadedFile(
                        '',
                        0,
                        UPLOAD_ERR_NO_FILE,
                        '',
                        ''
                    ),
                ],
            ],
            'Already Converted' => [
                [
                    'file' => new UploadedFile(
                        '/tmp/php/php1h4j1o',
                        123,
                        UPLOAD_ERR_OK,
                        'MyFile.txt',
                        'text/plain'
                    ),
                ],
                [
                    'file' => new UploadedFile(
                        '/tmp/php/php1h4j1o',
                        123,
                        UPLOAD_ERR_OK,
                        'MyFile.txt',
                        'text/plain'
                    ),
                ],
            ],
            'Already Converted array' => [
                [
                    'file' => [
                        new UploadedFile(
                            '/tmp/php/php1h4j1o',
                            123,
                            UPLOAD_ERR_OK,
                            'MyFile.txt',
                            'text/plain'
                        ),
                        new UploadedFile(
                            '',
                            0,
                            UPLOAD_ERR_NO_FILE,
                            '',
                            ''
                        ),
                    ],
                ],
                [
                    'file' => [
                        new UploadedFile(
                            '/tmp/php/php1h4j1o',
                            123,
                            UPLOAD_ERR_OK,
                            'MyFile.txt',
                            'text/plain'
                        ),
                        new UploadedFile(
                            '',
                            0,
                            UPLOAD_ERR_NO_FILE,
                            '',
                            ''
                        ),
                    ],
                ],
            ],
            'Multiple files' => [
                [
                    'text_file' => [
                        'name'     => 'MyFile.txt',
                        'type'     => 'text/plain',
                        'tmp_name' => '/tmp/php/php1h4j1o',
                        'error'    => '0',
                        'size'     => '123',
                    ],
                    'image_file' => [
                        'name'     => '',
                        'type'     => '',
                        'tmp_name' => '',
                        'error'    => '4',
                        'size'     => '0',
                    ],
                ],
                [
                    'text_file' => new UploadedFile(
                        '/tmp/php/php1h4j1o',
                        123,
                        UPLOAD_ERR_OK,
                        'MyFile.txt',
                        'text/plain'
                    ),
                    'image_file' => new UploadedFile(
                        '',
                        0,
                        UPLOAD_ERR_NO_FILE,
                        '',
                        ''
                    ),
                ],
            ],
            'Nested files' => [
                [
                    'file' => [
                        'name' => [
                            0 => 'MyFile.txt',
                            1 => 'Image.png',
                        ],
                        'type' => [
                            0 => 'text/plain',
                            1 => 'image/png',
                        ],
                        'tmp_name' => [
                            0 => '/tmp/php/hp9hskjhf',
                            1 => '/tmp/php/php1h4j1o',
                        ],
                        'error' => [
                            0 => '0',
                            1 => '0',
                        ],
                        'size' => [
                            0 => '123',
                            1 => '7349',
                        ],
                    ],
                    'nested' => [
                        'name' => [
                            'other' => 'Flag.txt',
                            'test'  => [
                                0 => 'Stuff.txt',
                                1 => '',
                            ],
                        ],
                        'type' => [
                            'other' => 'text/plain',
                            'test'  => [
                                0 => 'text/plain',
                                1 => '',
                            ],
                        ],
                        'tmp_name' => [
                            'other' => '/tmp/php/hp9hskjhf',
                            'test'  => [
                                0 => '/tmp/php/asifu2gp3',
                                1 => '',
                            ],
                        ],
                        'error' => [
                            'other' => '0',
                            'test'  => [
                                0 => '0',
                                1 => '4',
                            ],
                        ],
                        'size' => [
                            'other' => '421',
                            'test'  => [
                                0 => '32',
                                1 => '0',
                            ],
                        ],
                    ],
                ],
                [
                    'file' => [
                        0 => new UploadedFile(
                            '/tmp/php/hp9hskjhf',
                            123,
                            UPLOAD_ERR_OK,
                            'MyFile.txt',
                            'text/plain'
                        ),
                        1 => new UploadedFile(
                            '/tmp/php/php1h4j1o',
                            7349,
                            UPLOAD_ERR_OK,
                            'Image.png',
                            'image/png'
                        ),
                    ],
                    'nested' => [
                        'other' => new UploadedFile(
                            '/tmp/php/hp9hskjhf',
                            421,
                            UPLOAD_ERR_OK,
                            'Flag.txt',
                            'text/plain'
                        ),
                        'test' => [
                            0 => new UploadedFile(
                                '/tmp/php/asifu2gp3',
                                32,
                                UPLOAD_ERR_OK,
                                'Stuff.txt',
                                'text/plain'
                            ),
                            1 => new UploadedFile(
                                '',
                                0,
                                UPLOAD_ERR_NO_FILE,
                                '',
                                ''
                            ),
                        ],
                    ],
                ],
            ],
        ];
    }

    /**
     * @dataProvider dataNormalizeFiles
     *
     * @param mixed $files
     * @param mixed $expected
     */
    public function testNormalizeFiles($files, $expected)
    {
        $result = Util::normalizeFiles($files);
        self::assertEquals($expected, $result);
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Invalid value in files specification
     */
    public function testNormalizeFilesRaisesException()
    {
        Util::normalizeFiles(['test' => 'something']);
    }
}
