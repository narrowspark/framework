<?php
declare(strict_types=1);
namespace Viserio\Component\Parsers\Parser;

use RuntimeException;
use Sepia\PoParser as SepiaPoParser;
use Throwable;
use Viserio\Component\Contracts\Parsers\Exception\ParseException;
use Viserio\Component\Contracts\Parsers\Parser as ParserContract;

class PoParser implements ParserContract
{
    /**
     * Create a new Po loader.
     *
     * @codeCoverageIgnore
     */
    public function __construct()
    {
        if (! \class_exists(SepiaPoParser::class)) {
            throw new RuntimeException(
                'Loading translations from the Po Parser requires the Sepia PoParser component.'
            );
        }
    }

    /**
     * {@inheritdoc}
     *
     * @codeCoverageIgnore
     */
    public function parse(string $payload): array
    {
        if (! \file_exists($payload)) {
            throw new ParseException([
                'message' => 'File not found.',
            ]);
        }

        try {
            return SepiaPoParser::parseFile($payload);
        } catch (Throwable $exception) {
            throw new ParseException([
                'message' => 'Unable to parse the Po string',
            ]);
        }
    }
}
