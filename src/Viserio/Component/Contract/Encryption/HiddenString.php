<?php
declare(strict_types=1);
namespace Viserio\Component\Contract\Encryption;

interface HiddenString
{
    /**
     * Explicit invocation -- get the raw string value.
     *
     * @return string
     */
    public function getString(): string;
}
