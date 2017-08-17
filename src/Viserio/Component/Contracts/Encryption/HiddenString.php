<?php
declare(strict_types=1);
namespace Viserio\Component\Contracts\Encryption;

interface HiddenString
{
    /**
     * Explicit invocation -- get the raw string value.
     *
     * @return string
     */
    public function getString(): string;

    /**
     * Returns a copy of the string's internal value, which should be zeroed.
     * Optionally, it can return an empty string.
     *
     * @return string
     */
    public function __toString(): string;
}
