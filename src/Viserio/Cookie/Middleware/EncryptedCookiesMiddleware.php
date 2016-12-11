<?php
declare(strict_types=1);
namespace Viserio\Cookie\Middleware;

use Defuse\Crypto\Exception\EnvironmentIsBrokenException;
use Defuse\Crypto\Exception\WrongKeyOrModifiedCiphertextException;
use Interop\Http\Middleware\DelegateInterface;
use Interop\Http\Middleware\ServerMiddlewareInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Viserio\Contracts\Cookie\Cookie as CookieContract;
use Viserio\Contracts\Encryption\Encrypter as EncrypterContract;
use Viserio\Cookie\Cookie;
use Viserio\Cookie\RequestCookies;
use Viserio\Cookie\ResponseCookies;
use Viserio\Cookie\SetCookie;

class EncryptedCookiesMiddleware implements ServerMiddlewareInterface
{
    /**
     * The encrypter instance.
     *
     * @var \Viserio\Contracts\Encryption\Encrypter
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
     * @param \Viserio\Contracts\Encryption\Encrypter $encrypter
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

        foreach ($cookies->all() as $cookie) {
            $name = $cookie->getName();

            if ($this->isDisabled($name)) {
                continue;
            }

            try {
                $cookies = $cookies->forget($name);
                $cookie = $cookie->withValue($this->encrypter->decrypt($cookie->getValue()));

                $cookies = $cookies->add($cookie);
            } catch (EnvironmentIsBrokenException $exception) {
                $cookies = $cookies->add(new Cookie($name, null));
            } catch (WrongKeyOrModifiedCiphertextException $exception) {
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

        foreach ($cookies->all() as $cookie) {
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
     * @param \Viserio\Contracts\Cookie\Cookie $cookie
     * @param string                           $value
     *
     * @return \Viserio\Contracts\Cookie\Cookie
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
