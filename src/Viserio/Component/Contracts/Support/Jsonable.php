<?php
declare(strict_types=1);
namespace Viserio\Component\Contracts\Support;

interface Jsonable
{
    /**
     * Convert the object to its JSON representation.
     *
     * @param int $options
     *
     * @return string
     */
    public function toJson(int $options = 0): string;
}
