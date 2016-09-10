<?php
declare(strict_types=1);
namespace Viserio\Parsers\Formats;

use RuntimeException;
use Sepia\PoParser;
use Throwable;
use Viserio\Contracts\Parsers\Exception\ParseException;
use Viserio\Contracts\Parsers\Format as FormatContract;

class Po implements FormatContract
{
    /**
     * Create a new Po loader.
     */
    public function __construct()
    {
        if (! class_exists('Sepia\\PoParser')) {
            throw new RuntimeException(
                'Loading translations from the Po format requires the Sepia PoParser component.'
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
        if (! file_exists($payload)) {
            throw new ParseException([
                'message' => 'File not found.',
            ]);
        }

        try {
            return (new PoParser())->parseFile($filename);
        } catch (Throwable $exception) {
            throw new ParseException([
                'message' => 'Unable to parse the Po string',
            ]);
        }
    }
}
