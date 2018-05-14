<?php

declare(strict_types=1);

/**
 * This file is part of Narrowspark Framework.
 *
 * (c) Daniel Bannert <d.bannert@anolilab.de>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Viserio\Component\Cookie;

use Viserio\Component\Cookie\Traits\CookieValidatorTrait;
use Viserio\Contract\Cookie\Cookie as CookieContract;

final class SetCookie extends AbstractCookie
{
    use CookieValidatorTrait;

    /**
     * Create a new set-cookie instance.
     *
     * @param string                             $name       the name of the cookie
     * @param null|string                        $value      the value of the cookie
     * @param null|\DateTimeInterface|int|string $expiration the time the cookie expires
     * @param string                             $path       the path on the server in which the cookie will
     *                                                       be available on
     * @param null|string                        $domain     the domain that the cookie is available to
     * @param bool                               $secure     whether the cookie should only be transmitted
     *                                                       over a secure HTTPS connection from the client
     * @param bool                               $httpOnly   whether the cookie will be made accessible only.
     *                                                       through the HTTP protocol
     * @param bool|string                        $sameSite   Whether the cookie will be available for cross-site requests
     *
     * @throws \Viserio\Contract\Cookie\Exception\InvalidArgumentException
     */
    public function __construct(
        string $name,
        string $value = null,
        $expiration = 0,
        $path = '/',
        $domain = null,
        bool $secure = false,
        bool $httpOnly = false,
        $sameSite = false
    ) {
        $this->validateName($name);
        $this->validateValue($value);

        $this->name = $name;
        $this->value = $value;
        $this->maxAge = \is_int($expiration) ? $expiration : null;
        $this->expires = $this->normalizeExpires($expiration);
        $this->domain = $this->normalizeDomain($domain);
        $this->path = $this->normalizePath($path);
        $this->secure = $secure;
        $this->httpOnly = $httpOnly;
        $this->sameSite = $this->validateSameSite($sameSite);
    }

    /**
     * Returns the cookie as a string.
     *
     * @return string The cookie
     */
    public function __toString(): string
    {
        $cookieStringParts = [];

        $cookieStringParts = $this->appendFormattedNameAndValuePartIfSet($cookieStringParts);
        $cookieStringParts = $this->appendFormattedPathPartIfSet($cookieStringParts);
        $cookieStringParts = $this->appendFormattedDomainPartIfSet($cookieStringParts);
        $cookieStringParts = $this->appendFormattedMaxAgePartIfSet($cookieStringParts);
        $cookieStringParts = $this->appendFormattedSecurePartIfSet($cookieStringParts);
        $cookieStringParts = $this->appendFormattedHttpOnlyPartIfSet($cookieStringParts);
        $cookieStringParts = $this->appendFormattedSameSitePartIfSet($cookieStringParts);

        return \implode('; ', $cookieStringParts);
    }

    /**
     * {@inheritdoc}
     */
    public function withValue(string $value = null): CookieContract
    {
        $this->validateValue($value);

        $new = clone $this;
        $new->value = $value;

        return $new;
    }
}
