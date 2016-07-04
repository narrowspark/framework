<?php
namespace Viserio\Parsers\Formats;

use RuntimeException;
use Viserio\Contracts\Parsers\{
    Exception\DumpException,
    Exception\ParseException,
    Format as FormatContract
};
use Yosymfony\Toml\Exception\ParseException as TomlParseException;
use Yosymfony\Toml\Toml as YosymfonyToml;

class TOML implements FormatContract
{
    /**
     * Create a new Toml parser.
     *
     * @codeCoverageIgnore
     */
    public function __construct()
    {
        if (! class_exists('Yosymfony\\Toml\\Toml')) {
            throw new RuntimeException('Unable to read toml, the Toml Parser is not installed.');
        }
    }

    /**
     * {@inheritdoc}
     */
    public function parse(string $payload): array
    {
        try {
            return YosymfonyToml::parse($payload);
        } catch (TomlParseException $exception) {
            throw new ParseException([
                'message' => 'Unable to parse the TOML string.',
                'line' => $exception->getParsedLine(),
            ]);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function dump(array $data): string
    {
        return 'Not supported.';
    }
}
