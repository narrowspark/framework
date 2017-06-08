<?php
declare(strict_types=1);
namespace Viserio\Component\Parsers\Parsers;

use RuntimeException;
use Symfony\Component\Yaml\Exception\ParseException as YamlParseException;
use Symfony\Component\Yaml\Yaml as SymfonyYaml;
use Viserio\Component\Contracts\Parsers\Exceptions\ParseException;
use Viserio\Component\Contracts\Parsers\Parser as ParserContract;

class YamlParser implements ParserContract
{
    /**
     * Create a new Yaml parser.
     */
    public function __construct()
    {
        // @codeCoverageIgnoreStart
        if (! class_exists('Symfony\\Component\\Yaml\\Yaml')) {
            throw new RuntimeException('Unable to read yaml as the Symfony Yaml Component is not installed.');
        }
        // @codeCoverageIgnoreEnd
    }

    /**
     * {@inheritdoc}
     */
    public function parse(string $payload): array
    {
        try {
            return SymfonyYaml::parse(trim(preg_replace('/\t+/', '', $payload)));
        } catch (YamlParseException $exception) {
            throw new ParseException([
                'message'   => $exception->getMessage(),
                'exception' => $exception,
            ]);
        }
    }
}
