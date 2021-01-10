<?php

declare(strict_types=1);

/**
 * Copyright (c) 2018-2020 Daniel Bannert
 *
 * For the full copyright and license information, please view
 * the LICENSE.md file that was distributed with this source code.
 *
 * @see https://github.com/narrowspark/automatic
 */

namespace Viserio\Component\Cookie;

use Cake\Chronos\Chronos;
use Psr\Http\Message\ResponseInterface;
use Viserio\Contract\Cookie\Cookie as CookieContract;
use Viserio\Contract\Cookie\Exception\InvalidArgumentException;

final class ResponseCookies extends AbstractCookieCollector
{
    /**
     * Create a new cookie collection instance.
     *
     * @throws \Viserio\Contract\Cookie\Exception\InvalidArgumentException
     */
    public function __construct(array $cookies = [])
    {
        foreach ($cookies as $cookie) {
            if (! ($cookie instanceof CookieContract)) {
                throw new InvalidArgumentException(\sprintf('The object [%s] must implement [%s].', \get_class($cookie), CookieContract::class));
            }

            $this->cookies[$cookie->getName()] = $cookie;
        }
    }

    /**
     * Creates a Cookie instance from a Set-Cookie header value.
     *
     * @throws \Viserio\Contract\Cookie\Exception\InvalidArgumentException
     */
    public static function fromResponse(ResponseInterface $response): self
    {
        return new self(\array_map(static function ($setCookieString) {
            return self::fromStringCookie($setCookieString);
        }, $response->getHeader('set-cookie')));
    }

    /**
     * Render Cookies into a response.
     */
    public function renderIntoSetCookieHeader(ResponseInterface $response): ResponseInterface
    {
        $response = $response->withoutHeader('set-cookie');

        foreach ($this->cookies as $cookies) {
            $response = $response->withAddedHeader('set-cookie', (string) $cookies);
        }

        return $response;
    }

    /**
     * Creates a Cookie instance from a Set-Cookie header value.
     */
    protected static function fromStringCookie(string $string): CookieContract
    {
        $rawAttributes = self::splitOnAttributeDelimiter($string);

        [$cookieName, $cookieValue] = self::splitCookiePair(\array_shift($rawAttributes));

        $cookie = new SetCookie($cookieName);

        if (null !== $cookieValue) {
            $cookie = $cookie->withValue($cookieValue);
        }

        foreach ($rawAttributes as $value) {
            $rawAttributePair = \explode('=', $value, 2);
            $attributeKey = $rawAttributePair[0];
            $attributeValue = \count($rawAttributePair) > 1 ? $rawAttributePair[1] : null;
            $attributeKey = \strtolower($attributeKey);

            switch ($attributeKey) {
                case 'expires':
                    $cookie = $cookie->withExpires(new Chronos($attributeValue));

                    break;
                case 'max-age':
                    $age = \is_numeric($attributeValue) ? (int) $attributeValue : null;
                    $cookie = $cookie->withMaxAge($age);

                    break;
                case 'domain':
                    $cookie = $cookie->withDomain($attributeValue);

                    break;
                case 'path':
                    $cookie = $cookie->withPath($attributeValue ?? '/');

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
