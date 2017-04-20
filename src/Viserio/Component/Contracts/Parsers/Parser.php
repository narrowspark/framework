<?php
declare(strict_types=1);
namespace Viserio\Component\Contracts\Parsers;

interface Parser
{
    /**
     * Autodetect the payload data type using content-type value.
     *
     * @param string|null $format
     *
     * @return string Return the short format code (xml, json, ...)
     */
    public function getFormat(string $format = null): string;

    /**
     * Loads a file and output it content as array.
     *
     * @param string $payload
     *
     * @throws \Viserio\Component\Contracts\Parsers\Exception\ParseException
     * @throws \RuntimeException                                             if an error occurred during reading
     *
     * @return array
     */
    public function parse(string $payload): array;

    /**
     * Get supported parser.
     *
     * @param string $type
     *
     * @throws \Viserio\Component\Contracts\Parsers\Exception\NotSupportedException
     *
     * @return \Viserio\Component\Contracts\Parsers\Format
     */
    public function getParser(string $type): Format;
}
