<?php
declare(strict_types=1);
namespace Viserio\Component\Cookie\Middleware;

use Defuse\Crypto\Exception\EnvironmentIsBrokenException;
use Defuse\Crypto\Exception\WrongKeyOrModifiedCiphertextException;
use Interop\Http\ServerMiddleware\DelegateInterface;
use Interop\Http\ServerMiddleware\MiddlewareInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Viserio\Component\Contracts\Cookie\Cookie as CookieContract;
use Viserio\Component\Contracts\Encryption\Encrypter as EncrypterContract;
use Viserio\Component\Cookie\Cookie;
use Viserio\Component\Cookie\RequestCookies;
use Viserio\Component\Cookie\ResponseCookies;
use Viserio\Component\Cookie\SetCookie;

class EncryptedCookiesMiddleware implements MiddlewareInterface
{
    /**
     * The encrypter instance.
     *
     * @var \Viserio\Component\Contracts\Encryption\Encrypter
     */
    protected $encrypter;

    /**
     * The names of the cookies that should not be encrypted.
     *
     * @var array
     */
    protected $except = [];

    /**
     * Create a new encrypt Cookies instance.
     *
     * @param \Viserio\Component\Contracts\Encryption\Encrypter $encrypter
     */
    public function __construct(EncrypterContract $encrypter)
    {
        $this->encrypter = $encrypter;
    }

    /**
     * {@inheritdoc}
     */
    public function process(ServerRequestInterface $request, DelegateInterface $delegate)
    {
        $request = $this->decrypt($request);

        $response = $delegate->process($request);

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

        foreach ($cookies->getAll() as $cookie) {
            $name = $cookie->getName();

            if ($this->isDisabled($name)) {
                continue;
            }

            try {
                $cookies = $cookies->forget($name);
                $cookie  = $cookie->withValue($this->encrypter->decrypt($cookie->getValue()));

                $cookies = $cookies->add($cookie);
            } catch (EnvironmentIsBrokenException | WrongKeyOrModifiedCiphertextException $exception) {
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

        foreach ($cookies->getAll() as $cookie) {
            $name = $cookie->getName();

            if ($this->isDisabled($name)) {
                continue;
            }

            $cookies = $cookies->forget($name);

            $cookies = $cookies->add(
                $this->duplicate(
                    $cookie,
                    $this->encrypter->encrypt($cookie->getValue())
                )
            );
        }

        return $cookies->renderIntoSetCookieHeader($response);
    }

    /**
     * Duplicate a cookie with a new value.
     *
     * @param \Viserio\Component\Contracts\Cookie\Cookie $cookie
     * @param string                                     $value
     *
     * @return \Viserio\Component\Contracts\Cookie\Cookie
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
        return in_array($name, $this->except);
    }
}
