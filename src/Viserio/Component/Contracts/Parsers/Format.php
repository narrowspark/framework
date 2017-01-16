<?php
declare(strict_types=1);
namespace Viserio\Component\Contracts\Parsers;

interface Format
{
    /**
     * Loads a file and output it content as array.
     *
     * @param string $payload
     *
     * @throws \Viserio\Component\Contracts\Parsers\Exception\ParseException
     *
     * @return array
     */
    public function parse(string $payload): array;
}
