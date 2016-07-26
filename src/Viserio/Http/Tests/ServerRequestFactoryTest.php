<?php
declare(strict_types=1);
namespace Viserio\Http\Tests;

use Viserio\Http\{
    ServerRequestFactory,
    Uri,
    UploadedFile
};

class ServerRequestFactoryTest extends \PHPUnit_Framework_TestCase
{
    public function dataGetUriFromGlobals()
    {
        $server = [
            'PHP_SELF' => '/blog/article.php',
            'GATEWAY_INTERFACE' => 'CGI/1.1',
            'SERVER_ADDR' => 'Server IP: 127.0.0.1',
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
            'SERVER_PORT' => '80',
            'SERVER_SIGNATURE' => 'Version signature: 5.123',
            'SCRIPT_NAME' => '/blog/article.php',
            'REQUEST_URI' => '/blog/article.php?id=10&user=foo',
        ];

        return [
            'Normal request' => [
                'http://www.narrowspark.com/blog/article.php?id=10&user=foo',
                $server,
            ],
            'Secure request' => [
                'https://www.narrowspark.com/blog/article.php?id=10&user=foo',
                array_merge($server, ['HTTPS' => 'on', 'SERVER_PORT' => '443']),
            ],
            'HTTP_HOST missing' => [
                'http://www.narrowspark.com/blog/article.php?id=10&user=foo',
                array_merge($server, ['HTTP_HOST' => null]),
            ],
            'No query String' => [
                'http://www.narrowspark.com/blog/article.php',
                array_merge($server, ['REQUEST_URI' => '/blog/article.php', 'QUERY_STRING' => '']),
            ],
            'Different port' => [
                'http://www.narrowspark.com:8324/blog/article.php?id=10&user=foo',
                array_merge($server, ['SERVER_PORT' => '8324']),
            ],
            'Empty server variable' => [
                '',
                [],
            ],
        ];
    }

    /**
     * @dataProvider dataGetUriFromGlobals
     */
    public function testGetUriFromGlobals($expected, $serverParams)
    {
        $_SERVER = $serverParams;
        $serverRequest = (new ServerRequestFactory())->createServerRequestFromGlobals();
        $this->assertEquals(new Uri($expected), $serverRequest->getUri());
    }

    public function testFromGlobals()
    {
        $_SERVER = [
            'PHP_SELF' => '/blog/article.php',
            'GATEWAY_INTERFACE' => 'CGI/1.1',
            'SERVER_ADDR' => 'Server IP: 127.0.0.1',
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
            'SERVER_PORT' => '80',
            'SERVER_SIGNATURE' => 'Version signature: 5.123',
            'SCRIPT_NAME' => '/blog/article.php',
            'REQUEST_URI' => '/blog/article.php?id=10&user=foo',
        ];
        $_COOKIE = [
            'logged-in' => 'yes!',
        ];
        $_POST = [
            'name' => 'Pesho',
            'email' => 'pesho@example.com',
        ];
        $_GET = [
            'id' => 10,
            'user' => 'foo',
        ];
        $_FILES = [
            'file' => [
                'name' => 'MyFile.txt',
                'type' => 'text/plain',
                'tmp_name' => '/tmp/php/php1h4j1o',
                'error' => UPLOAD_ERR_OK,
                'size' => 123,
            ],
        ];

        $server = (new ServerRequestFactory())->createServerRequestFromGlobals();

        $this->assertEquals('POST', $server->getMethod());
        $this->assertEquals(['Host' => ['www.narrowspark.com']], $server->getHeaders());
        $this->assertEquals('', (string) $server->getBody());
        $this->assertEquals('1.0', $server->getProtocolVersion());
        $this->assertEquals($_COOKIE, $server->getCookieParams());
        $this->assertEquals($_POST, $server->getParsedBody());
        $this->assertEquals($_GET, $server->getQueryParams());
        $this->assertEquals(
            new Uri('http://www.narrowspark.com/blog/article.php?id=10&user=foo'),
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

        $this->assertEquals($expectedFiles, $server->getUploadedFiles());
    }
}
