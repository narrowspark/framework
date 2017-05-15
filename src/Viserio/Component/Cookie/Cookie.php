<?php
declare(strict_types=1);
namespace Viserio\Component\Cookie;

use Viserio\Component\Contracts\Support\Stringable as StringableContract;
use Viserio\Component\Cookie\Traits\CookieValidatorTrait;

final class Cookie implements StringableContract
{
    use CookieValidatorTrait;

    /**
     * @var string
     */
    protected $name;

    /**
     * @var string|null
     */
    protected $value;

    /**
     * Create a new cookie instance.
     *
     * @param string      $name  the name of the cookie
     * @param string|null $value the value of the cookie
     *
     * @throws \InvalidArgumentException
     */
    public function __construct(string $name, ?string $value = null)
    {
        $this->validateName($name);
        $this->validateValue($value);

        $this->name  = $name;
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
     * @param string|null $value
     *
     * @return $this
     */
    public function withValue(?string $value = null): Cookie
    {
        $this->validateValue($value);

        $new        = clone $this;
        $new->value = $value;

        return $new;
    }

    /**
     * Returns the value.
     *
     * @return string|null
     */
    public function getValue(): ?string
    {
        return $this->value;
    }
}
