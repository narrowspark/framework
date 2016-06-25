<?php
namespace Viserio\Parsers\Formats;

use Exception;
use Sepia\PoParser;
use Viserio\Contracts\Parsers\{
    Exception\ParseException,
    Format as FormatContract
};

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
        } catch (Exception $exception) {
            throw new ParseException([
                'message' => 'Unable to parse the Po string'
            ]);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function dump(array $data): string
    {
        //
    }
}
