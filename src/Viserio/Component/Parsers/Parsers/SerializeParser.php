<?php
declare(strict_types=1);
namespace Viserio\Component\Parsers\Formats;

use Throwable;
use Viserio\Component\Contracts\Parsers\Exception\ParseException;
use Viserio\Component\Contracts\Parsers\Format as FormatContract;

class SerializeParser implements FormatContract
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
