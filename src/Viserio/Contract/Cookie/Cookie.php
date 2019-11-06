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

namespace Viserio\Contract\Cookie;

use DateTimeInterface;

interface Cookie
{
    /**
     * Const for samesite.
     */
    public const SAMESITE_STRICT = 'strict';
    public const SAMESITE_LAX = 'lax';

    /**
     * Returns the name.
     *
     * @return string
     */
    public function getName(): string;

    /**
     * Sets the value.
     *
     * @param string $value
     *
     * @return self
     */
    public function withValue(string $value = ''): self;

    /**
     * Returns the value.
     *
     * @return null|string
     */
    public function getValue(): ?string;

    /**
     * Checks if there is a value.
     *
     * @return bool
     */
    public function hasValue(): bool;

    /**
     * Sets the max age.
     *
     * @param null|int $maxAge
     *
     * @return self
     */
    public function withMaxAge(?int $maxAge = null): self;

    /**
     * Returns the max age.
     *
     * @return null|int
     */
    public function getMaxAge(): ?int;

    /**
     * Checks if there is a max age.
     *
     * @return bool
     */
    public function hasMaxAge(): bool;

    /**
     * Sets the expires.
     *
     * @param null|DateTimeInterface|int|string $expires
     *
     * @return self
     */
    public function withExpires($expires): self;

    /**
     * Returns the expiration time.
     *
     * @return null|int
     */
    public function getExpiresTime(): ?int;

    /**
     * Checks if there is an expiration time.
     *
     * @return bool
     */
    public function hasExpires(): bool;

    /**
     * Checks if the cookie is expired.
     *
     * @return bool
     */
    public function isExpired(): bool;

    /**
     * Sets the domain.
     *
     * @param null|string $domain
     *
     * @return self
     */
    public function withDomain(?string $domain = null): self;

    /**
     * Returns the domain.
     *
     * @return null|string
     */
    public function getDomain(): ?string;

    /**
     * Checks if there is a domain.
     *
     * @return bool
     */
    public function hasDomain(): bool;

    /**
     * Sets the path.
     *
     * @param string $path
     *
     * @return self
     */
    public function withPath(string $path = '/'): self;

    /**
     * Returns the path.
     *
     * @return string
     */
    public function getPath(): string;

    /**
     * Sets the secure.
     *
     * @param bool $secure
     *
     * @return self
     */
    public function withSecure(bool $secure): self;

    /**
     * Checks if HTTPS is required.
     *
     * @return bool
     */
    public function isSecure(): bool;

    /**
     * Sets the HTTP Only.
     *
     * @param bool $httpOnly
     *
     * @return self
     */
    public function withHttpOnly(bool $httpOnly): self;

    /**
     * Checks if it is HTTP-only.
     *
     * @return bool
     */
    public function isHttpOnly(): bool;

    /**
     * Whether the cookie will be available for cross-site requests.
     *
     * @param bool|string $sameSite
     *
     * @return self
     */
    public function withSameSite($sameSite): self;

    /**
     * Checks if the cookie value should be sent with a SameSite attribute.
     *
     * @return bool
     */
    public function isSameSite(): bool;

    /**
     * Gets the SameSite attribute.
     *
     * @return null|bool|string
     */
    public function getSameSite();

    /**
     * It matches a path.
     *
     * @param string $path
     *
     * @return bool
     *
     * @see http://tools.ietf.org/html/rfc6265#section-5.1.4
     */
    public function matchPath(string $path): bool;

    /**
     * Checks if it matches with another cookie.
     *
     * @param self $cookie
     *
     * @return bool
     */
    public function matchCookie(Cookie $cookie): bool;

    /**
     * Matches a domain.
     *
     * @param string $domain
     *
     * @return bool
     *
     * @see http://tools.ietf.org/html/rfc6265#section-5.1.3
     */
    public function matchDomain(string $domain): bool;
}
