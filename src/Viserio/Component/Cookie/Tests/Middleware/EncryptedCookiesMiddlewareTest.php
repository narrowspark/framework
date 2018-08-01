<?php
declare(strict_types=1);
namespace Viserio\Component\Cookie\Tests\Middleware;

use Narrowspark\TestingHelper\Middleware\CallableMiddleware;
use Narrowspark\TestingHelper\Middleware\Dispatcher;
use Narrowspark\TestingHelper\Phpunit\MockeryTestCase;
use ParagonIE\Halite\HiddenString;
use ParagonIE\Halite\Symmetric\Crypto;
use ParagonIE\Halite\Symmetric\EncryptionKey;
use Viserio\Component\Cookie\Cookie;
use Viserio\Component\Cookie\Middleware\EncryptedCookiesMiddleware;
use Viserio\Component\Cookie\RequestCookies;
use Viserio\Component\Cookie\ResponseCookies;
use Viserio\Component\Cookie\SetCookie;
use Viserio\Component\Http\ServerRequest;
use Viserio\Component\HttpFactory\ResponseFactory;

/**
 * @internal
 */
final class EncryptedCookiesMiddlewareTest extends MockeryTestCase
{
    /**
     * @var \ParagonIE\Halite\Symmetric\EncryptionKey
     */
    private $key;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->key = new EncryptionKey(new HiddenString(\str_repeat('A', 32)));
    }

    public function testEncryptedCookieRequest(): void
    {
        $key     = $this->key;
        $request = new ServerRequest('/');

        $dispatcher = new Dispatcher([
            new CallableMiddleware(function ($request, $handler) use ($key) {
                $cookies = RequestCookies::fromRequest($request);
                $encryptedValue = Crypto::encrypt(new HiddenString('test'), $key);
                $cookies = $cookies->add(new Cookie('encrypted', $encryptedValue));

                return $handler->handle($cookies->renderIntoCookieHeader($request));
            }),
            new EncryptedCookiesMiddleware($key),
            new CallableMiddleware(function ($request) {
                $cookies = RequestCookies::fromRequest($request);

                static::assertSame('encrypted', $cookies->get('encrypted')->getName());
                static::assertSame('test', $cookies->get('encrypted')->getValue());

                return (new ResponseFactory())->createResponse(200);
            }),
        ]);

        $dispatcher->dispatch($request);
    }

    public function testEncryptedCookieResponse(): void
    {
        $request = new ServerRequest('/');

        $dispatcher = new Dispatcher([
            new EncryptedCookiesMiddleware($this->key),
            new CallableMiddleware(function () {
                $response = (new ResponseFactory())->createResponse();

                $cookies = ResponseCookies::fromResponse($response);
                $cookies = $cookies->add(new SetCookie('encrypted', 'test'));

                return $cookies->renderIntoSetCookieHeader($response);
            }),
        ]);

        $response       = $dispatcher->dispatch($request);
        $cookies        = ResponseCookies::fromResponse($response);
        $decryptedValue = Crypto::decrypt($cookies->get('encrypted')->getValue(), $this->key);

        static::assertSame('encrypted', $cookies->get('encrypted')->getName());
        static::assertSame('test', $decryptedValue->getString());
    }
}
