<?php
declare(strict_types=1);
namespace Viserio\Component\Cookie;

use Psr\Http\Message\ServerRequestInterface;
use Viserio\Component\Contract\Cookie\Exception\InvalidArgumentException;

final class RequestCookies extends AbstractCookieCollector
{
    /**
     * Create a new cookie collection instance.
     *
     * @param array $cookies
     *
     * @throws \Viserio\Component\Contract\Cookie\Exception\InvalidArgumentException
     */
    public function __construct(array $cookies = [])
    {
        foreach ($cookies as $cookie) {
            if (! ($cookie instanceof Cookie)) {
                throw new InvalidArgumentException(\sprintf(
                    'The object [%s] must be an instance of [%s].',
                    \get_class($cookie),
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
     * @throws \Viserio\Component\Contract\Cookie\Exception\InvalidArgumentException
     *
     * @return self
     */
    public static function fromRequest(ServerRequestInterface $request): self
    {
        return new static(self::listFromCookieString($request->getHeaderLine('cookie')));
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
        $cookieString = \implode('; ', $this->cookies);

        return $request->withHeader('cookie', $cookieString);
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

        return \array_map(function ($cookiePair) {
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
        [$name, $value] = self::splitCookiePair($string);

        $cookie = new Cookie($name);

        if (null !== $value) {
            $cookie = $cookie->withValue($value);
        }

        return $cookie;
    }
}
