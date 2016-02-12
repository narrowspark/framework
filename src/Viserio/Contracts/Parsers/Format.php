<?php
namespace Viserio\Contracts\Parsers;

interface Format
{
    /**
     * Loads a file and output it content as array.
     *
     * @param string $payload
     *
     * @throws \Viserio\Contracts\Parsers\Exception\ParseException
     *
     * @return array|string|null
     */
    public function parse($payload);

    /**
     * Dumps a array into a string.
     *
     * @param array $data
     *
     * @throws \Viserio\Contracts\Parsers\Exception\DumpException If dumping fails
     *
     * @return string|false
     */
    public function dump(array $data);
}
