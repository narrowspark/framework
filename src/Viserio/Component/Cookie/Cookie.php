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

use InvalidArgumentException;
use Viserio\Component\Cookie\Traits\CookieValidatorTrait;
use Viserio\Contract\Support\Stringable as StringableContract;

final class Cookie implements StringableContract
{
    use CookieValidatorTrait;

    /** @var string */
    private $name;

    /** @var null|string */
    private $value;

    /**
     * Create a new cookie instance.
     *
     * @param string      $name  the name of the cookie
     * @param null|string $value the value of the cookie
     *
     * @throws InvalidArgumentException
     */
    public function __construct(string $name, ?string $value = null)
    {
        $this->validateName($name);
        $this->validateValue($value);

        $this->name = $name;
        $this->value = $value;
    }

    /**
     * Returns the cookie as a string.
     *
     * @return string The cookie
     */
    public function __toString(): string
    {
        $name = \urlencode($this->name) . '=';

        return $name . \urlencode($this->getValue());
    }

    /**
     * Returns the name.
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Returns the value.
     */
    public function getValue(): string
    {
        return (string) $this->value;
    }

    /**
     * Sets the value.
     *
     * @return $this
     */
    public function withValue(?string $value = null): Cookie
    {
        $this->validateValue($value);

        $new = clone $this;
        $new->value = $value;

        return $new;
    }
}
