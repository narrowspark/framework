<?php
declare(strict_types=1);
namespace Viserio\Cookie;

use Psr\Http\Message\ServerRequestInterface;
use Viserio\Contracts\Cookie\Cookie as CookieContract;

class RequestCookies extends AbstractCookieCollector
{
    /**
     * Creates a Cookie instance from a Set-Cookie header value.
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request
     *
     * @return self
     */
    public static function fromRequest(ServerRequestInterface $request): RequestCookies
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

        $request = $request->withHeader('Cookie', $cookieString);

        return $request;
    }

    /**
     * Create a list of Cookies from a Cookie header value string.
     *
     * @param string $string
     *
     * @return array
     */
    protected static function listFromCookieString(string $string)
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
     * @return \Viserio\Contracts\Cookie\Cookie
     */
    protected static function oneFromCookiePair(string $string): CookieContract
    {
        list($name, $value) = self::splitCookiePair($string);

        return new Cookie($name, $value);
    }
}
