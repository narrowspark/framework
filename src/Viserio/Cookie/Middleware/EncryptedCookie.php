<?php
namespace Viserio\Cookie\Middleware;

use Visero\Contracts\Encryption\Encrypter as EncrypterContract;
use Visero\Contracts\Encryption\DecryptException;
use Psr\Http\Message\RequestInterface as RequestContract;
use Psr\Http\Message\ResponseInterface as ResponseContract;

class EncryptCookies
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
     * Create a new CookieGuard instance.
     *
     * @param \Viserio\Contracts\Encryption\Encrypter $encrypter
     *
     * @return void
     */
    public function __construct(EncrypterContract $encrypter)
    {
        $this->encrypter = $encrypter;
    }

    /**
     * Disable encryption for the given cookie name(s).
     *
     * @param string|array $cookieName
     *
     * @return void
     */
    public function disableFor($cookieName)
    {
        if (is_array($cookieName)) {
            $this->except = array_merge($this->except, $cookieName);
        }

        $this->except[] = $cookieName;
    }

    /**
     * Duplicate a cookie with a new value.
     *
     * @param \Viserio\Cookie\Cookie $cookie
     * @param mixed $value
     *
     * @return \Viserio\Cookie\Cookie
     */
    protected function duplicate(Cookie $cookie, $value)
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
     * Determine whether encryption has been disabled for the given cookie.
     *
     * @param string $name
     *
     * @return bool
     */
    public function isDisabled($name)
    {
        return in_array($name, $this->except);
    }

    /**
     * Decrypt the cookies on the request.
     *
     * @param \Psr\Http\Message\RequestInterface $request
     *
     * @return \Psr\Http\Message\RequestInterface
     */
    protected function decrypt(RequestContract $request)
    {
        foreach ($request->cookies as $key => $c) {
            if ($this->isDisabled($key)) {
                continue;
            }

            try {
                $request->cookies->set($key, $this->decryptCookie($c));
            } catch (DecryptException $e) {
                $request->cookies->set($key, null);
            }
        }

        return $request;
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
    protected function decryptArray(array $cookie)
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
    protected function encrypt(ResponseContract $response)
    {
        foreach ($response->headers->getCookies() as $cookie) {
            if ($this->isDisabled($cookie->getName())) {
                continue;
            }

            $response->headers->setCookie(
                $this->duplicate(
                    $cookie,
                    $this->encrypter->encrypt($cookie->getValue())
                )
            );
        }

        return $response;
    }
}
