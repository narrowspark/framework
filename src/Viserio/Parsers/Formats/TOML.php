<?php
namespace Viserio\Parsers\Formats;

use RuntimeException;
use Viserio\Contracts\Parsers\Exception\ParseException;
use Viserio\Contracts\Parsers\Format as FormatContract;
use Yosymfony\Toml\Exception\ParseException as TomlParseException;
use Yosymfony\Toml\Toml as YosymfonyToml;
use Yosymfony\Toml\TomlBuilder;

class TOML implements FormatContract
{
    /**
     * Create a new Toml parser.
     */
    public function __construct()
    {
        if (!class_exists('Yosymfony\\Toml\\Toml')) {
            throw new RuntimeException('Unable to read toml, the Toml Parser is not installed.');
        }
    }

    /**
     * {@inheritdoc}
     */
    public function parse($payload)
    {
        try {
            return YosymfonyToml::Parse($payload);
        } catch (TomlParseException $exception) {
            throw new ParseException([
                'message' => 'Unable to parse the TOML string',
                'line' => $exception->getParsedLine()
            ]);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function dump(array $data)
    {
        $tb = new TomlBuilder();

        return 'Not supported';
    }
}
