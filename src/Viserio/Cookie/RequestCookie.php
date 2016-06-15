<?php
namespace Viserio\Cookie;

use DateTime;
use Psr\Http\Message\ServerRequestInterface;
use \Viserio\Contracts\Cookie\Cookie as CookieContract;

class RequestCookie
{
    /**
     * Creates a Cookie instance from a Set-Cookie header value.
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request
     *
     * @return \Viserio\Contracts\Cookie\Cookie
     */
    public function fromSetCookieHeader(ServerRequestInterface $request): CookieContract
    {
        return $this->fromStringCookie($request->getHeader('Set-Cookie'));
    }

    /**
     * Creates a Cookie instance from a Set-Cookie header value.
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request
     *
     * @return \Viserio\Contracts\Cookie\Cookie
     */
    public function fromCookieHeader(ServerRequestInterface $request): CookieContract
    {
        return $this->fromStringCookie($request->getHeaderLine('Cookie'));
    }

    /**
     * Creates a Cookie instance from a Set-Cookie header value.
     *
     * @param string $string
     *
     * @return \Viserio\Contracts\Cookie\Cookie
     */
    protected function fromStringCookie(string $string): CookieContract
    {
        $rawAttributes = $this->splitOnAttributeDelimiter($string);

        list($cookieName, $cookieValue) = $this->splitCookiePair($rawAttributes[0]);

        $cookie = new Cookie($cookieName);

        if (! is_null($cookieValue)) {
            $cookie = $cookie->withValue($cookieValue);
        }

        foreach ($rawAttributes as $value) {
            $rawAttributePair = explode('=', $value, 2);
            $attributeKey = $rawAttributePair[0];
            $attributeValue = count($rawAttributePair) > 1 ? $rawAttributePair[1] : null;
            $attributeKey = strtolower($attributeKey);

            switch ($attributeKey) {
                case 'expires':
                    $cookie = $cookie->withExpires(new DateTime($attributeValue));
                    break;
                case 'max-age':
                    $age = is_numeric($attributeValue) ? (int) $attributeValue : null;
                    $cookie = $cookie->withMaxAge($age);
                    break;
                case 'domain':
                    $cookie = $cookie->withDomain($attributeValue);
                    break;
                case 'path':
                    $cookie = $cookie->withPath($attributeValue);
                    break;
                case 'secure':
                    $cookie = $cookie->withSecure(true);
                    break;
                case 'httponly':
                    $cookie = $cookie->withHttpOnly(true);
                    break;
            }
        }

        return $cookie;
    }

    /**
     * spplit string on attributes delimiter to array.
     *
     * @param string $string
     *
     * @return array
     */
    protected function splitOnAttributeDelimiter(string $string): array
    {
        return array_filter(preg_split('@\s*[;]\s*@', $string));
    }

    /**
     * Split a string to array.
     *
     * @param string $string
     *
     * @return array
     */
    protected function splitCookiePair(string $string): array
    {
        $pairParts = explode('=', $string, 2);

        if (count($pairParts) === 1) {
            $pairParts[1] = '';
        }

        return array_map(function ($part) {
            return urldecode($part);
        }, $pairParts);
    }
}
