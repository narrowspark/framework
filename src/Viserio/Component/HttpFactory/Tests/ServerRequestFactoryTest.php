<?php
declare(strict_types=1);
namespace Viserio\Component\HttpFactory\Tests;

use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;
use Viserio\Component\Http\Uri;
use Viserio\Component\HttpFactory\ServerRequestFactory;

class ServerRequestFactoryTest extends TestCase
{
    private $factory;

    public function setUp()
    {
        $this->factory = new ServerRequestFactory();
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
        $serverRequest = $this->factory->createServerRequest($serverParams);

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
        ];

        $server = $this->factory->createServerRequest($_SERVER);

        self::assertEquals('POST', $server->getMethod());
        self::assertEquals([
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
    public function testCreateServerRequest($server)
    {
        $method  = $server['REQUEST_METHOD'];
        $uri     = "http://{$server['HTTP_HOST']}{$server['REQUEST_URI']}";
        $request = $this->factory->createServerRequest($server);
        $this->assertServerRequest($request, $method, $uri);
    }

    /**
     * @dataProvider dataServer
     *
     * @param mixed $server
     */
    public function testCreateServerRequestWithOverridenMethod($server)
    {
        $method  = 'OPTIONS';
        $uri     = "http://{$server['HTTP_HOST']}{$server['REQUEST_URI']}";
        $request = $this->factory->createServerRequest($server, $method);
        $this->assertServerRequest($request, $method, $uri);
    }

    /**
     * @dataProvider dataServer
     *
     * @param mixed $server
     */
    public function testCreateServerRequestWithOverridenUri($server)
    {
        $method  = $server['REQUEST_METHOD'];
        $uri     = 'https://example.com/foobar?bar=2&foo=false';
        $request = $this->factory->createServerRequest($server, null, $uri);
        $this->assertServerRequest($request, $method, $uri);
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
        $request = $this->factory->createServerRequest([], $method, Uri::createFromString($uri));
        $this->assertServerRequest($request, $method, $uri);
    }

    /**
     * @backupGlobals enabled
     */
    public function testCreateServerRequestDoesNotReadServerSuperglobal()
    {
        $_SERVER      = ['HTTP_X_FOO' => 'bar'];
        $request      = $this->factory->createServerRequest([], 'POST', 'http://example.org/test');
        $serverParams = $request->getServerParams();
        $this->assertNotEquals($_SERVER, $serverParams);
        $this->assertArrayNotHasKey('HTTP_X_FOO', $serverParams);
    }

    public function testCreateServerRequestDoesNotReadCookieSuperglobal()
    {
        $_COOKIE = ['foo' => 'bar'];
        $request = $this->factory->createServerRequest([], 'POST', 'http://example.org/test');
        $this->assertEmpty($request->getCookieParams());
    }

    public function testCreateServerRequestDoesNotReadGetSuperglobal()
    {
        $_GET    = ['foo' => 'bar'];
        $request = $this->factory->createServerRequest([], 'POST', 'http://example.org/test');
        $this->assertEmpty($request->getQueryParams());
    }

    public function testCreateServerRequestDoesNotReadFilesSuperglobal()
    {
        $_FILES  = [['name' => 'foobar.dat', 'type' => 'application/octet-stream', 'tmp_name' => '/tmp/php45sd3f', 'error' => UPLOAD_ERR_OK, 'size' => 4]];
        $request = $this->factory->createServerRequest([], 'POST', 'http://example.org/test');
        $this->assertEmpty($request->getUploadedFiles());
    }

    public function testCreateServerRequestDoesNotReadPostSuperglobal()
    {
        $_POST   = ['foo' => 'bar'];
        $request = $this->factory->createServerRequest(['CONTENT_TYPE' => 'application/x-www-form-urlencoded'], 'POST', 'http://example.org/test');
        $this->assertEmpty($request->getParsedBody());
    }

    protected function assertServerRequest($request, $method, $uri)
    {
        $this->assertInstanceOf(ServerRequestInterface::class, $request);
        $this->assertSame($method, $request->getMethod());
        $this->assertSame($uri, (string) $request->getUri());
    }
}
