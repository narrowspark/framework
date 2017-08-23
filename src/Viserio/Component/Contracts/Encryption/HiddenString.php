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
}
