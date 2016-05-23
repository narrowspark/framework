<?php
namespace Viserio\Contracts\Support;

interface Xmlable
{
    /**
     * Convert the object to its XML representation.
     *
     * @return string
     */
    public function toXml(): string;
}
