<?php
declare(strict_types=1);
namespace Viserio\Component\Parsers\Formats;

use Throwable;
use Viserio\Component\Contracts\Parsers\Dumper as DumperContract;
use Viserio\Component\Contracts\Parsers\Exception\ParseException;
use Viserio\Component\Contracts\Parsers\Format as FormatContract;

class PhpParser implements FormatContract
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
