<?php
declare(strict_types=1);
namespace Viserio\Component\HttpFactory\Tests;

use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;
use Viserio\Component\HttpFactory\ServerRequestFactory;

/**
 * @internal
 */
final class ServerRequestFactoryTest extends TestCase
{
    /**
     * @var \Psr\Http\Message\ServerRequestFactoryInterface
     */
    private $factory;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        $this->factory = new ServerRequestFactory();
    }

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

    public function dataServer(): array
    {
        $data = [];

        foreach ($this->dataMethods() as $methodData) {
            $data[] = [
                [
                    'REQUEST_METHOD' => $methodData[0],
                    'REQUEST_URI'    => '/test',
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
    public function testCreateServerRequest($server): void
    {
        $method  = $server['REQUEST_METHOD'];
        $uri     = "http://{$server['HTTP_HOST']}{$server['REQUEST_URI']}?{$server['QUERY_STRING']}";
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
        $method  = $server['REQUEST_METHOD'];
        $uri     = "http://{$server['HTTP_HOST']}{$server['REQUEST_URI']}?{$server['QUERY_STRING']}";
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
        $method  = $server['REQUEST_METHOD'];
        $uri     = "http://{$server['HTTP_HOST']}{$server['REQUEST_URI']}?{$server['QUERY_STRING']}";
        $request = $this->factory->createServerRequest($method, $this->createUri($uri));
        $this->assertServerRequest($request, $method, $uri);
    }

    /**
     * @backupGlobals enabled
     */
    public function testCreateServerRequestDoesNotReadServerSuperGlobal(): void
    {
        $_SERVER = ['HTTP_X_FOO' => 'bar'];
        $server  = [
            'REQUEST_METHOD' => 'PUT',
            'REQUEST_URI'    => '/test',
            'QUERY_STRING'   => 'super=0',
            'HTTP_HOST'      => 'example.org',
        ];
        $request      = $this->factory->createServerRequest('PUT', '/test', $server);
        $serverParams = $request->getServerParams();
        static::assertNotEquals($_SERVER, $serverParams);
        static::assertArrayNotHasKey('HTTP_X_FOO', $serverParams);
    }

    public function testCreateServerRequestDoesNotReadCookieSuperGlobal(): void
    {
        $_COOKIE = ['foo' => 'bar'];
        $request = $this->factory->createServerRequest('POST', 'http://example.org/test');
        static::assertEmpty($request->getCookieParams());
    }

    public function testCreateServerRequestDoesNotReadGetSuperGlobal(): void
    {
        $_GET    = ['foo' => 'bar'];
        $request = $this->factory->createServerRequest('POST', 'http://example.org/test');
        static::assertEmpty($request->getQueryParams());
    }

    public function testCreateServerRequestDoesNotReadFilesSuperGlobal(): void
    {
        $_FILES  = [['name' => 'foobar.dat', 'type' => 'application/octet-stream', 'tmp_name' => '/tmp/php45sd3f', 'error' => \UPLOAD_ERR_OK, 'size' => 4]];
        $request = $this->factory->createServerRequest('POST', 'http://example.org/test');
        static::assertEmpty($request->getUploadedFiles());
    }

    public function testCreateServerRequestDoesNotReadPostSuperGlobal(): void
    {
        $_POST   = ['foo' => 'bar'];
        $request = $this->factory->createServerRequest('POST', 'http://example.org/test');
        static::assertEmpty($request->getParsedBody());
    }

    protected function assertServerRequest($request, $method, $uri): void
    {
        static::assertInstanceOf(ServerRequestInterface::class, $request);
        static::assertSame($method, $request->getMethod());
        static::assertSame($uri, (string) $request->getUri());
    }
}
