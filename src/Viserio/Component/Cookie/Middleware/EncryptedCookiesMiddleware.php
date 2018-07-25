<?php
declare(strict_types=1);
namespace Viserio\Component\Cookie\Middleware;

use ParagonIE\Halite\Alerts\InvalidMessage;
use ParagonIE\Halite\HiddenString;
use ParagonIE\Halite\Symmetric\Crypto;
use ParagonIE\Halite\Symmetric\EncryptionKey;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Viserio\Component\Contract\Cookie\Cookie as CookieContract;
use Viserio\Component\Cookie\Cookie;
use Viserio\Component\Cookie\RequestCookies;
use Viserio\Component\Cookie\ResponseCookies;
use Viserio\Component\Cookie\SetCookie;

class EncryptedCookiesMiddleware implements MiddlewareInterface
{
    /**
     * The encrypter key instance.
     *
     * @var \ParagonIE\Halite\Symmetric\EncryptionKey
     */
    protected $key;

    /**
     * The names of the cookies that should not be encrypted.
     *
     * @var array
     */
    protected $except = [];

    /**
     * Create a new encrypt Cookies instance.
     *
     * @param \ParagonIE\Halite\Symmetric\EncryptionKey $key
     */
    public function __construct(EncryptionKey $key)
    {
        $this->key = $key;
    }

    /**
     * Hide this from var_dump(), etc.
     *
     * @return array
     */
    public function __debugInfo(): array
    {
        return [
            'key' => 'private',
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $request = $this->decrypt($request);

        $response = $handler->handle($request);

        return $this->encrypt($response);
    }

    /**
     * Decrypt the cookies on the request.
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request
     *
     * @return \Psr\Http\Message\ServerRequestInterface
     */
    protected function decrypt(ServerRequestInterface $request): ServerRequestInterface
    {
        $cookies = RequestCookies::fromRequest($request);

        /** @var Cookie $cookie */
        foreach ($cookies->getAll() as $cookie) {
            $name = $cookie->getName();

            if ($this->isDisabled($name)) {
                continue;
            }

            try {
                $decryptedValue = Crypto::decrypt($cookie->getValue(), $this->key);
                $cookies        = $cookies->forget($name);
                $cookie         = $cookie->withValue($decryptedValue->getString());

                $cookies = $cookies->add($cookie);
            } catch (InvalidMessage $exception) {
                $cookies = $cookies->add(new Cookie($name, null));
            }
        }

        return $cookies->renderIntoCookieHeader($request);
    }

    /**
     * Encrypt the cookies on an outgoing response.
     *
     * @param \Psr\Http\Message\ResponseInterface $response
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    protected function encrypt(ResponseInterface $response): ResponseInterface
    {
        $cookies = ResponseCookies::fromResponse($response);

        /** @var SetCookie $cookie */
        foreach ($cookies->getAll() as $cookie) {
            $name = $cookie->getName();

            if ($this->isDisabled($name)) {
                continue;
            }

            $cookies        = $cookies->forget($name);
            $encryptedValue = Crypto::encrypt(
                new HiddenString($cookie->getValue()),
                $this->key
            );

            $cookies = $cookies->add(
                $this->duplicate(
                    $cookie,
                    $encryptedValue
                )
            );
        }

        return $cookies->renderIntoSetCookieHeader($response);
    }

    /**
     * Duplicate a cookie with a new value.
     *
     * @param \Viserio\Component\Contract\Cookie\Cookie $cookie
     * @param string                                    $value
     *
     * @return \Viserio\Component\Contract\Cookie\Cookie
     */
    protected function duplicate(CookieContract $cookie, string $value): CookieContract
    {
        return new SetCookie(
            $cookie->getName(),
            $value,
            $cookie->getExpiresTime(),
            $cookie->getPath(),
            $cookie->getDomain(),
            $cookie->isSecure(),
            $cookie->isHttpOnly()
        );
    }

    /**
     * Determine whether encryption has been disabled for the given cookie.
     *
     * @param string $name
     *
     * @return bool
     */
    protected function isDisabled(string $name): bool
    {
        return \in_array($name, $this->except, true);
    }
}
