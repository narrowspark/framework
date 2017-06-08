<?php
declare(strict_types=1);
namespace Viserio\Component\Parsers\Tests\Fixtures;

use Viserio\Component\Contracts\Parsers\Parser as ParserContract;

class TextParser implements ParserContract
{
    /**
     * {@inheritdoc}
     */
    public function parse(string $payload): array
    {
        return (array) $payload;
    }
}
