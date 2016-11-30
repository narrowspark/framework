<?php
declare(strict_types=1);
namespace Viserio\Cookie;

use Viserio\Contracts\Support\Stringable as StringableContract;
use Viserio\Cookie\Traits\CookieValidatorTratis;

final class Cookie implements StringableContract
{
    use CookieValidatorTratis;

    /**
     * Create a new cookie instance.
     *
     * @param string      $name  The name of the cookie.
     * @param string|null $value The value of the cookie.
     *
     * @throws \InvalidArgumentException
     */
    public function __construct(string $name, string $value = null)
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
    public function __toString()
    {
        $name = urlencode($this->name) . '=';

        return $name . urlencode($this->getValue());
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
     * Sets the value.
     *
     * @param string $value
     *
     * @return $this
     */
    public function withValue(string $value = null): Cookie
    {
        $this->validateValue($value);

        $new = clone $this;
        $new->value = $value;

        return $new;
    }

    /**
     * Returns the value.
     *
     * @return string|null
     */
    public function getValue()
    {
        return $this->value;
    }
}
