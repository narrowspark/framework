<?php
declare(strict_types=1);
namespace Viserio\Component\Cookie;

class AbstractCookieCollector
{
    /**
     * All stored cookies.
     *
     * @var array
     */
    protected $cookies = [];

    /**
     * Checking if request cookie exist.
     *
     * @param string $name
     *
     * @return bool
     */
    public function has(string $name): bool
    {
        return isset($this->cookies[$name]);
    }

    /**
     * Get a request cookie.
     *
     * @param string $name
     *
     * @return \Viserio\Component\Contracts\Cookie\Cookie|null
     */
    public function get(string $name)
    {
        if (! $this->has($name)) {
            return;
        }

        return $this->cookies[$name];
    }

    /**
     * Get all request cookies.
     *
     * @return array
     */
    public function getAll(): array
    {
        return array_values($this->cookies);
    }

    /**
     * Add a request cookie to the stack.
     *
     * @param \Viserio\Component\Contracts\Cookie\Cookie|\Viserio\Component\Cookie\Cookie $cookie
     *
     * @return $this
     */
    public function add($cookie)
    {
        $clone                              = clone $this;
        $clone->cookies[$cookie->getName()] = $cookie;

        return $clone;
    }

    /**
     * Forget a request cookie.
     *
     * @param string $name
     *
     * @return $this
     */
    public function forget(string $name)
    {
        $clone = clone $this;

        if (! $clone->has($name)) {
            return $clone;
        }

        unset($clone->cookies[$name]);

        return $clone;
    }

    /**
     * spplit string on attributes delimiter to array.
     *
     * @param string $string
     *
     * @return array
     */
    protected static function splitOnAttributeDelimiter(string $string): array
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
    protected static function splitCookiePair(string $string): array
    {
        $pairParts = explode('=', $string, 2);

        if (count($pairParts) === 1) {
            $pairParts[1] = '';
        }

        return array_map(function ($part) {
            if ($part === null) {
                return '';
            }

            return urldecode($part);
        }, $pairParts);
    }
}
