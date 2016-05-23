<?php
namespace Viserio\Parsers\Formats;

use Viserio\Contracts\Parsers\Exception\DumpException;
use Viserio\Contracts\Parsers\Exception\ParseException;
use Viserio\Contracts\Parsers\Format as FormatContract;

class QueryStr implements FormatContract
{
    /**
     * {@inheritdoc}
     */
    public function parse(string $payload): array
    {
        parse_str(trim($payload), $querystr);

        return $querystr;
    }

    /**
     * {@inheritdoc}
     */
    public function dump(array $data): string
    {
        return http_build_query($data);
    }
}
