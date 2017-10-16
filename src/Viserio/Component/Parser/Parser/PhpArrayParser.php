<?php
declare(strict_types=1);
namespace Viserio\Component\Parser\Parser;

use Throwable;
use Viserio\Component\Contract\Parser\Exception\ParseException;
use Viserio\Component\Contract\Parser\Parser as ParserContract;

class PhpArrayParser implements ParserContract
{
    /**
     * {@inheritdoc}
     */
    public function parse(string $payload): array
    {
        if (! \file_exists($payload)) {
            throw new ParseException(['message' => \sprintf('No such file [%s] found.', $payload)]);
        }

        try {
            $data = require $payload;
        } catch (Throwable $exception) {
            throw new ParseException(
                [
                    'message'   => \sprintf('An exception was thrown by file [%s].', $payload),
                    'exception' => $exception,
                ]
            );
        }

        return (array) $data;
    }
}
