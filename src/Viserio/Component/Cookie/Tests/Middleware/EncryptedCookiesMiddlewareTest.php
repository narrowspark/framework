<?php
declare(strict_types=1);
namespace Viserio\Component\Cookie\Tests\Middleware;

use Narrowspark\TestingHelper\Middleware\CallableMiddleware;
use Narrowspark\TestingHelper\Middleware\Dispatcher;
use Narrowspark\TestingHelper\Phpunit\MockeryTestCase;
use Viserio\Component\Cookie\Cookie;
use Viserio\Component\Cookie\Middleware\EncryptedCookiesMiddleware;
use Viserio\Component\Cookie\RequestCookies;
use Viserio\Component\Cookie\ResponseCookies;
use Viserio\Component\Cookie\SetCookie;
use ParagonIE\Halite\Encrypter;
use ParagonIE\Halite\HiddenString;
use ParagonIE\Halite\Key;
use Viserio\Component\HttpFactory\ResponseFactory;
use Viserio\Component\HttpFactory\ServerRequestFactory;

class EncryptedCookiesMiddlewareTest extends MockeryTestCase
{
    /**
     * @var \ParagonIE\Halite\Encrypter
     */
    private $encrypter;

    protected function setUp(): void
    {
        parent::setUp();

        $key = new Key(new HiddenString(\str_repeat('A', 32)));

        $this->encrypter = new Encrypter($key);
    }

    public function tearDown(): void
    {
        unset($_SERVER['SERVER_ADDR']);
    }

    public function testEncryptedCookieRequest(): void
    {
        $encrypter             = $this->encrypter;
        $server                = $_SERVER;
        $server['SERVER_ADDR'] = '127.0.0.1';
        unset($server['PHP_SELF']);

        $request = (new ServerRequestFactory())->createServerRequestFromArray($server);

        $dispatcher = new Dispatcher([
            new CallableMiddleware(function ($request, $handler) use ($encrypter) {
                $cookies = RequestCookies::fromRequest($request);
                $encryptedValue = $encrypter->encrypt(new HiddenString('test'));
                $cookies = $cookies->add(new Cookie('encrypted', $encryptedValue));

                return $handler->handle($cookies->renderIntoCookieHeader($request));
            }),
            new EncryptedCookiesMiddleware($encrypter),
            new CallableMiddleware(function ($request, $handler) {
                $cookies = RequestCookies::fromRequest($request);

                self::assertSame('encrypted', $cookies->get('encrypted')->getName());
                self::assertSame('test', $cookies->get('encrypted')->getValue());

                return (new ResponseFactory())->createResponse(200);
            }),
        ]);

        $dispatcher->dispatch($request);
    }

    public function testEncryptedCookieResponse(): void
    {
        $server                = $_SERVER;
        $server['SERVER_ADDR'] = '127.0.0.1';
        unset($server['PHP_SELF']);

        $request = (new ServerRequestFactory())->createServerRequestFromArray($server);

        $dispatcher = new Dispatcher([
            new EncryptedCookiesMiddleware($this->encrypter),
            new CallableMiddleware(function () {
                $response = (new ResponseFactory())->createResponse();

                $cookies = ResponseCookies::fromResponse($response);
                $cookies = $cookies->add(new SetCookie('encrypted', 'test'));

                return $cookies->renderIntoSetCookieHeader($response);
            }),
        ]);

        $response       = $dispatcher->dispatch($request);
        $cookies        = ResponseCookies::fromResponse($response);
        $decryptedValue = $this->encrypter->decrypt($cookies->get('encrypted')->getValue());

        self::assertSame('encrypted', $cookies->get('encrypted')->getName());
        self::assertSame('test', $decryptedValue->getString());
    }
}
