<?php
declare(strict_types=1);
namespace Viserio\Component\Contracts\Support;

interface Stringable
{
    /**
     * Get the instance as an string.
     *
     * @return string
     */
    public function __toString();
}
