<?php
declare(strict_types=1);
namespace Viserio\Component\Cookie;

use Cake\Chronos\Chronos;
use Psr\Http\Message\ResponseInterface;
use RuntimeException;
use Viserio\Component\Contracts\Cookie\Cookie as CookieContract;

class ResponseCookies extends AbstractCookieCollector
{
    /**
     * Create a new cookie collection instance.
     *
     * @param array $cookies
     *
     * @throws \RuntimeException
     */
    public function __construct(array $cookies = [])
    {
        foreach ($cookies as $cookie) {
            if (! ($cookie instanceof CookieContract)) {
                throw new RuntimeException(sprintf(
                    'The object [%s] must implement Viserio\Component\Contracts\Cookie\Cookie',
                    get_class($cookie)
                ));
            }

            $this->cookies[$cookie->getName()] = $cookie;
        }
    }

    /**
     * Creates a Cookie instance from a Set-Cookie header value.
     *
     * @param \Psr\Http\Message\ResponseInterface $response
     *
     * @return $this
     */
    public static function fromResponse(ResponseInterface $response)
    {
        return new static(array_map(function ($setCookieString) {
            return self::fromStringCookie($setCookieString);
        }, $response->getHeader('Set-Cookie')));
    }

    /**
     * Render Cookies into a response.
     *
     * @param \Psr\Http\Message\ResponseInterface $response
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function renderIntoSetCookieHeader(ResponseInterface $response): ResponseInterface
    {
        $response = $response->withoutHeader('Set-Cookie');

        foreach ($this->cookies as $cookies) {
            $response = $response->withAddedHeader('Set-Cookie', (string) $cookies);
        }

        return $response;
    }

    /**
     * Creates a Cookie instance from a Set-Cookie header value.
     *
     * @param string $string
     *
     * @return \Viserio\Component\Contracts\Cookie\Cookie
     */
    protected static function fromStringCookie(string $string): CookieContract
    {
        $rawAttributes = self::splitOnAttributeDelimiter($string);

        list($cookieName, $cookieValue) = self::splitCookiePair(array_shift($rawAttributes));

        $cookie = new SetCookie($cookieName);

        if (! is_null($cookieValue)) {
            $cookie = $cookie->withValue($cookieValue);
        }

        foreach ($rawAttributes as $value) {
            $rawAttributePair = explode('=', $value, 2);
            $attributeKey     = $rawAttributePair[0];
            $attributeValue   = count($rawAttributePair) > 1 ? $rawAttributePair[1] : null;
            $attributeKey     = mb_strtolower($attributeKey);

            switch ($attributeKey) {
                case 'expires':
                    $cookie = $cookie->withExpires(new Chronos($attributeValue));
                    break;
                case 'max-age':
                    $age    = is_numeric($attributeValue) ? (int) $attributeValue : null;
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
                case 'samesite':
                    $cookie = $cookie->withSameSite($attributeValue);
                    break;
            }
        }

        return $cookie;
    }
}
