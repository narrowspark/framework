<?php
declare(strict_types=1);
namespace Viserio\Component\Cookie;

use InvalidArgumentException;
use Psr\Http\Message\ServerRequestInterface;

class RequestCookies extends AbstractCookieCollector
{
    /**
     * Create a new cookie collection instance.
     *
     * @param array $cookies
     *
     * @throws \InvalidArgumentException
     */
    public function __construct(array $cookies = [])
    {
        foreach ($cookies as $cookie) {
            if (! ($cookie instanceof Cookie)) {
                throw new InvalidArgumentException(sprintf(
                    'The object [%s] must be an instance of [%s].',
                    get_class($cookie),
                    Cookie::class
                ));
            }

            $this->cookies[$cookie->getName()] = $cookie;
        }
    }

    /**
     * Creates a Cookie instance from a Set-Cookie header value.
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request
     *
     * @return self
     */
    public static function fromRequest(ServerRequestInterface $request): self
    {
        return new static(self::listFromCookieString($request->getHeaderLine('Cookie')));
    }

    /**
     * Render Cookies into a Request.
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request
     *
     * @return \Psr\Http\Message\ServerRequestInterface
     */
    public function renderIntoCookieHeader(ServerRequestInterface $request): ServerRequestInterface
    {
        $cookieString = implode('; ', $this->cookies);
        $request      = $request->withHeader('Cookie', $cookieString);

        return $request;
    }

    /**
     * Create a list of Cookies from a Cookie header value string.
     *
     * @param string $string
     *
     * @return array
     */
    protected static function listFromCookieString(string $string): array
    {
        $cookies = self::splitOnAttributeDelimiter($string);

        return array_map(function ($cookiePair) {
            return self::oneFromCookiePair($cookiePair);
        }, $cookies);
    }

    /**
     * Create one Cookie from a cookie key/value header value string.
     *
     * @param string $string
     *
     * @return \Viserio\Component\Cookie\Cookie
     */
    protected static function oneFromCookiePair(string $string): Cookie
    {
        list($name, $value) = self::splitCookiePair($string);

        $cookie = new Cookie($name);

        if ($value !== null || $value !== '') {
            $cookie = $cookie->withValue($value);
        }

        return $cookie;
    }
}
