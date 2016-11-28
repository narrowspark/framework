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
        return $this->encrypt($delegate->process($this->decrypt($request)));
    }

    /**
     * Disable encryption for the given cookie name(s).
     *
     * @param string|array $cookieName
     */
    public function disableFor($cookieName)
    {
        if (is_array($cookieName)) {
            $this->except = array_merge($this->except, $cookieName);
        }

        $this->except[] = $cookieName;
    }

    /**
     * Determine whether encryption has been disabled for the given cookie.
     *
     * @param string $name
     *
     * @return bool
     */
    public function isDisabled(string $name): bool
    {
        return in_array($name, $this->except);
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
        return new Cookie(
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
     * Decrypt the cookies on the request.
     *
     * @param \Psr\Http\Message\RequestInterface $request
     *
     * @return \Psr\Http\Message\RequestInterface
     */
    protected function decrypt(RequestInterface $request): RequestInterface
    {
        $cookies = RequestCookies::fromRequest($request);

        foreach ($cookies->getAll() as $key => $cookie) {
            if ($this->isDisabled($key)) {
                continue;
            }

            try {
                $cookies = $cookies->forget($key);
                $cookie = $cookie->withValue($this->decryptCookie($cookie->getValue()));

                $cookies = $cookies->add($cookie);
            } catch (EnvironmentIsBrokenException $exception) {
                $cookies = $cookies->add(new Cookie($key, null));
            } catch (WrongKeyOrModifiedCiphertextException $exception) {
                $cookies = $cookies->add(new Cookie($key, null));
            }
        }

        return $cookies->renderIntoCookieHeader($request);
    }

    /**
     * Decrypt the given cookie and return the value.
     *
     * @param string|array $cookie
     *
     * @return string|array
     */
    protected function decryptCookie($cookie)
    {
        return is_array($cookie) ?
            $this->decryptArray($cookie) :
            $this->encrypter->decrypt($cookie);
    }

    /**
     * Decrypt an array based cookie.
     *
     * @param array $cookie
     *
     * @return array
     */
    protected function decryptArray(array $cookie): array
    {
        $decrypted = [];

        foreach ($cookie as $key => $value) {
            if (is_string($value)) {
                $decrypted[$key] = $this->encrypter->decrypt($value);
            }
        }

        return $decrypted;
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

        foreach ($cookies->getAll() as $key => $cookie) {
            if ($this->isDisabled($cookie->getName())) {
                continue;
            }

            $cookies = $cookies->forget($key);

            $cookies = $cookies->add(
                $key,
                $this->duplicate(
                    $cookie,
                    $this->encrypter->encrypt($cookie->getValue())
                )
            );
        }

        return $cookies->renderIntoSetCookieHeader($response);
    }
}
