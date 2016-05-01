<?php
namespace Viserio\Contracts\Parsers;

Interface Parser
{
    /**
     * Loads a file and output it content as array.
     *
     * @param string $payload
     *
     * @throws \Viserio\Contracts\Parsers\Exception\ParseException
     *
     * @return array
     */
    public function parse($payload);
}
