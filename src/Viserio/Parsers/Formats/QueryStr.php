<?php
namespace Viserio\Parsers\Formats;

use Viserio\Contracts\Filesystem\Filesystem as FilesystemContract;
use Viserio\Contracts\Parser\Exception\DumpException;
use Viserio\Contracts\Parser\Exception\ParseException;
use Viserio\Contracts\Parsers\Format as FormatContract;

class QueryStr implements FormatContract
{
    /**
     * {@inheritdoc}
     */
    public function parse($payload)
    {
        try {
            parse_str(trim($payload), $querystr);

            return $querystr;
        } catch (Exception $exception) {
            throw new ParseException('Failed to parse query string data');
        }
    }

    /**
     * {@inheritdoc}
     */
    public function dump(array $data)
    {
        return http_build_query($data);
    }
}
