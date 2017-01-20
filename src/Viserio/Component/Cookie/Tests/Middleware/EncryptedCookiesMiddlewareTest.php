<?php
declare(strict_types=1);
namespace Viserio\Component\Cookie\Tests\Middleware;

use Defuse\Crypto\Key;
use Mockery as Mock;
use Narrowspark\TestingHelper\Middleware\CallableMiddleware;
use Narrowspark\TestingHelper\Middleware\Dispatcher;
use Narrowspark\TestingHelper\Traits\MockeryTrait;
use PHPUnit\Framework\TestCase;
use Viserio\Component\Cookie\Cookie;
use Viserio\Component\Cookie\Middleware\EncryptedCookiesMiddleware;
use Viserio\Component\Cookie\RequestCookies;
use Viserio\Component\Cookie\ResponseCookies;
use Viserio\Component\Cookie\SetCookie;
use Viserio\Component\Encryption\Encrypter;
use Viserio\Component\HttpFactory\ResponseFactory;
use Viserio\Component\HttpFactory\ServerRequestFactory;

class EncryptedCookiesMiddlewareTest extends TestCase
{
    use MockeryTrait;

    public function tearDown()
    {
        unset($_SERVER['SERVER_ADDR']);

        parent::tearDown();

        $this->allowMockingNonExistentMethods(true);

        // Verify Mockery expectations.
        Mock::close();
    }

    public function testEncryptedCookieRequest()
    {
        $encrypter = new Encrypter(Key::createNewRandomKey());

        $server                = $_SERVER;
        $server['SERVER_ADDR'] = '127.0.0.1';
        unset($server['PHP_SELF']);

        $request   = (new ServerRequestFactory())->createServerRequest($server);

        $dispatcher = new Dispatcher([
            new CallableMiddleware(function ($request, $delegate) use ($encrypter) {
                $cookies = RequestCookies::fromRequest($request);
                $cookies = $cookies->add(new Cookie('encrypted', $encrypter->encrypt('test')));

                return $delegate->process($cookies->renderIntoCookieHeader($request));
            }),
            new EncryptedCookiesMiddleware($encrypter),
            new CallableMiddleware(function ($request, $delegate) {
                $cookies = RequestCookies::fromRequest($request);

                self::assertSame('encrypted', $cookies->get('encrypted')->getName());
                self::assertSame('test', $cookies->get('encrypted')->getValue());

                return (new ResponseFactory())->createResponse(200);
            }),
        ]);

        $dispatcher->dispatch($request);
    }

    public function testEncryptedCookieResponse()
    {
        $encrypter = new Encrypter(Key::createNewRandomKey());

        $server                = $_SERVER;
        $server['SERVER_ADDR'] = '127.0.0.1';
        unset($server['PHP_SELF']);

        $request   = (new ServerRequestFactory())->createServerRequest($server);

        $dispatcher = new Dispatcher([
            new EncryptedCookiesMiddleware($encrypter),
            new CallableMiddleware(function ($request, $delegate) {
                $response = (new ResponseFactory())->createResponse(200);

                $cookies = ResponseCookies::fromResponse($response);
                $cookies = $cookies->add(new SetCookie('encrypted', 'test'));

                return $cookies->renderIntoSetCookieHeader($response);
            }),
        ]);

        $response = $dispatcher->dispatch($request);
        $cookies  = ResponseCookies::fromResponse($response);

        self::assertSame('encrypted', $cookies->get('encrypted')->getName());
        self::assertSame('test', $encrypter->decrypt($cookies->get('encrypted')->getValue()));
    }
}
