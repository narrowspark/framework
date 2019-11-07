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
     *
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Returns the value.
     *
     * @return string
     */
    public function getValue(): string
    {
        return (string) $this->value;
    }

    /**
     * Sets the value.
     *
     * @param null|string $value
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
