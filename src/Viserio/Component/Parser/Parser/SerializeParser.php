<?php
declare(strict_types=1);
namespace Viserio\Component\Parser\Parser;

use Throwable;
use Viserio\Component\Contract\Parser\Exception\ParseException;
use Viserio\Component\Contract\Parser\Parser as ParserContract;

class SerializeParser implements ParserContract
{
    /**
     * {@inheritdoc}
     */
    public function parse(string $payload): array
    {
        try {
            return \unserialize(\trim($payload), ['allowed_classes' => false]);
        } catch (Throwable $exception) {
            throw new ParseException([
                'message' => sprintf('Failed to parse serialized Data; %s.', $exception->getMessage()),
            ]);
        }
    }
}
