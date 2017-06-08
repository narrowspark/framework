<?php
declare(strict_types=1);
namespace Viserio\Component\Parsers\Parsers;

use Throwable;
use Viserio\Component\Contracts\Parsers\Exceptions\ParseException;
use Viserio\Component\Contracts\Parsers\Parser as ParserContract;

class PhpParser implements ParserContract
{
    /**
     * {@inheritdoc}
     */
    public function parse(string $payload): array
    {
        if (! file_exists($payload)) {
            throw new ParseException(['message' => sprintf('No such file [%s] found.', $payload)]);
        }

        try {
            $data = require $payload;
        } catch (Throwable $exception) {
            throw new ParseException(
                [
                    'message'   => sprintf('An exception was thrown by file [%s].', $payload),
                    'exception' => $exception,
                ]
            );
        }

        return (array) $data;
    }
}
