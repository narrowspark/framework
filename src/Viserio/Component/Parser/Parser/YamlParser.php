<?php
declare(strict_types=1);
namespace Viserio\Component\Parser\Parser;

use RuntimeException;
use Symfony\Component\Yaml\Exception\ParseException as YamlParseException;
use Symfony\Component\Yaml\Yaml as SymfonyYaml;
use Viserio\Component\Contract\Parser\Exception\ParseException;
use Viserio\Component\Contract\Parser\Parser as ParserContract;

class YamlParser implements ParserContract
{
    /**
     * Create a new Yaml parser.
     *
     * @throws \RuntimeException
     */
    public function __construct()
    {
        // @codeCoverageIgnoreStart
        if (! \class_exists(SymfonyYaml::class)) {
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
            return SymfonyYaml::parse(\trim(\preg_replace('/\t+/', '', $payload)));
        } catch (YamlParseException $exception) {
            throw new ParseException([
                'message'   => $exception->getMessage(),
                'exception' => $exception,
            ]);
        }
    }
}
