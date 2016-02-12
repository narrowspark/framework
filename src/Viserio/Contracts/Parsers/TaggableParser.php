<?php
namespace Viserio\Contracts\Parsers;

interface TaggableParser
{
    /**
     * Tag delimiter.
     *
     * @var string
     */
    const TAG_DELIMITER = '::';

    /**
     * Loads a file and output content as array.
     *
     * @param string       $payload
     * @param string|array $group
     *
     * @throws \Viserio\Contracts\Parsers\Exception\ParseException
     *
     * @return array|string|null
     */
    public function parse($payload, $taggedKey);

    /**
     * Format a data file for saving.
     *
     * @param array $data
     *
     * @throws \Viserio\Contracts\Parsers\Exception\DumpException If dumping fails
     *
     * @return string|false data export
     */
    public function dump(array $data);
}
