<?php
declare(strict_types=1);
namespace Viserio\Component\Parsers\Formats;

use Viserio\Component\Contracts\Parsers\Format as FormatContract;

class QueryStrParser implements FormatContract
{
    /**
     * {@inheritdoc}
     */
    public function parse(string $payload): array
    {
        parse_str(trim($payload), $querystr);

        return $querystr;
    }
}
