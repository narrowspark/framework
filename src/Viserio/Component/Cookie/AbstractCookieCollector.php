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

use Viserio\Contract\Cookie\Exception\InvalidArgumentException;

abstract class AbstractCookieCollector
{
    /**
     * All stored cookies.
     *
     * @var array
     */
    protected $cookies = [];

    /**
     * Checking if request cookie exist.
     */
    public function has(string $name): bool
    {
        return isset($this->cookies[$name]);
    }

    /**
     * Get a request cookie.
     *
     * @return null|\Viserio\Component\Cookie\Cookie|\Viserio\Contract\Cookie\Cookie
     */
    public function get(string $name)
    {
        if (! $this->has($name)) {
            return null;
        }

        return $this->cookies[$name];
    }

    /**
     * Get all request cookies.
     */
    public function getAll(): array
    {
        return \array_values($this->cookies);
    }

    /**
     * Add a request cookie to the stack.
     *
     * @param \Viserio\Component\Cookie\Cookie|\Viserio\Contract\Cookie\Cookie $cookie
     *
     * @throws \Viserio\Contract\Cookie\Exception\InvalidArgumentException
     *
     * @return $this
     */
    public function add($cookie): self
    {
        if ($cookie instanceof Cookie || $cookie instanceof SetCookie) {
            $clone = clone $this;
            $clone->cookies[$cookie->getName()] = $cookie;

            return $clone;
        }

        throw new InvalidArgumentException(\sprintf('The object [%s] must be an instance of [%s] or [%s].', \get_class($cookie), Cookie::class, SetCookie::class));
    }

    /**
     * Remove a request cookie.
     *
     * @return $this
     */
    public function remove(string $name): self
    {
        $clone = clone $this;

        if (! $clone->has($name)) {
            return $clone;
        }

        unset($clone->cookies[$name]);

        return $clone;
    }

    /**
     * Split string on attributes delimiter to array.
     */
    protected static function splitOnAttributeDelimiter(string $string): array
    {
        return \array_filter(\preg_split('@\s*[;]\s*@', $string));
    }

    /**
     * Split a string to array.
     */
    protected static function splitCookiePair(string $string): array
    {
        $pairParts = \explode('=', $string, 2);

        if (\count($pairParts) === 1) {
            $pairParts[1] = '';
        }

        return \array_map(static function ($part) {
            /** @codeCoverageIgnoreStart */
            if ($part === null) {
                return '';
            }
            /** @codeCoverageIgnoreEnd */

            return \urldecode($part);
        }, $pairParts);
    }
}
