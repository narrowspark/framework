<?php
declare(strict_types=1);
namespace Viserio\Component\HttpFactory\Tests;

use PHPUnit\Framework\TestCase;
use Viserio\Component\Http\UploadedFile;
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
            'SERVER_PORT'          => '80',
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
                array_merge($server, ['HTTPS' => 'on', 'SERVER_PORT' => '443']),
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

        self::assertEquals(new Uri($expected), $serverRequest->getUri());
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
            'SERVER_PORT'          => '80',
            'SERVER_SIGNATURE'     => 'Version signature: 5.123',
            'SCRIPT_NAME'          => '/doc/framwork.php',
            'REQUEST_URI'          => '/doc/framwork.php?id=10&user=foo',
        ];
        $_COOKIE = [
            'logged-in' => 'yes!',
        ];
        $_POST = [
            'name'  => 'Narrowspark',
            'email' => 'parrowspark@example.com',
        ];
        $_GET = [
            'id'   => 10,
            'user' => 'foo',
        ];
        $_FILES = [
            'file' => [
                'name'     => 'MyFile.txt',
                'type'     => 'text/plain',
                'tmp_name' => '/tmp/php/php1h4j1o',
                'error'    => UPLOAD_ERR_OK,
                'size'     => 123,
            ],
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
        self::assertEquals($_COOKIE, $server->getCookieParams());
        self::assertEquals($_POST, $server->getParsedBody());
        self::assertEquals($_GET, $server->getQueryParams());
        self::assertEquals(
            new Uri('https://www.narrowspark.com/doc/framwork.php?id=10&user=foo'),
            $server->getUri()
        );

        $expectedFiles = [
            'file' => new UploadedFile(
                '/tmp/php/php1h4j1o',
                123,
                UPLOAD_ERR_OK,
                'MyFile.txt',
                'text/plain'
            ),
        ];

        self::assertEquals($expectedFiles, $server->getUploadedFiles());
    }
}
