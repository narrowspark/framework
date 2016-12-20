<?php
declare(strict_types=1);
namespace Viserio\Parsers\Formats;

use RuntimeException;
use Symfony\Component\Yaml\Exception\ParseException as YamlParseException;
use Symfony\Component\Yaml\Yaml as SymfonyYaml;
use Viserio\Contracts\Parsers\Dumper as DumperContract;
use Viserio\Contracts\Parsers\Exception\ParseException;
use Viserio\Contracts\Parsers\Format as FormatContract;

class YAML implements FormatContract, DumperContract
{
    /**
     * Create a new Yaml parser.
     */
    public function __construct()
    {
        // @codeCoverageIgnoreStart
        if (!class_exists('Symfony\\Component\\Yaml\\Yaml')) {
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
            return SymfonyYaml::parse(
                trim(preg_replace('/\t+/', '', $payload))
            );
        } catch (YamlParseException $exception) {
            throw new ParseException([
                'message' => $exception->getMessage(),
            ]);
        }
    }

    /**
     * {@inheritdoc}
     *
     * @codeCoverageIgnore
     */
    public function dump(array $data): string
    {
        return SymfonyYaml::dump($data);
    }
}
