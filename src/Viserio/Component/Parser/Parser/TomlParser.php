<?php
declare(strict_types=1);
namespace Viserio\Component\Parser\Parser;

use Viserio\Component\Contract\Parser\Exception\ParseException;
use Viserio\Component\Contract\Parser\Exception\RuntimeException;
use Viserio\Component\Contract\Parser\Parser as ParserContract;
use Yosymfony\Toml\Exception\ParseException as TomlParseException;
use Yosymfony\Toml\Toml as YosymfonyToml;

class TomlParser implements ParserContract
{
    /**
     * Create a new Toml parser.
     *
     * @throws \Viserio\Component\Contract\Parser\Exception\RuntimeException
     *
     * @codeCoverageIgnore
     */
    public function __construct()
    {
        if (! \class_exists(YosymfonyToml::class)) {
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
                'line'    => $exception->getParsedLine(),
            ]);
        }
    }
}
