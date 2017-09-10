<?php
declare(strict_types=1);
namespace Viserio\Component\Contract\Support;

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
