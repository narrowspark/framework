<?php
declare(strict_types=1);
namespace Viserio\Component\Parsers\Parsers;

use Throwable;
use Viserio\Component\Contracts\Parsers\Exception\ParseException;
use Viserio\Component\Contracts\Parsers\Parser as ParserContract;

class SerializeParser implements ParserContract
{
    /**
     * {@inheritdoc}
     */
    public function parse(string $payload): array
    {
        try {
            return unserialize(trim($payload));
        } catch (Throwable $exception) {
            throw new ParseException([
                'message' => 'Failed to parse serialized Data',
            ]);
        }
    }
}
