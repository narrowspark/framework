<?php
declare(strict_types=1);
namespace Viserio\Component\Parser\Parser;

use Viserio\Component\Contract\Parser\Parser as ParserContract;

class QueryStrParser implements ParserContract
{
    /**
     * {@inheritdoc}
     */
    public function parse(string $payload): array
    {
        \parse_str(\trim($payload), $querystr);

        return $querystr;
    }
}
