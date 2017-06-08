<?php
declare(strict_types=1);
namespace Viserio\Component\Parsers\Dumpers;

use InvalidArgumentException;
use RuntimeException;
use Symfony\Component\Yaml\Yaml as SymfonyYaml;
use Viserio\Component\Contracts\Parsers\Dumper as DumperContract;
use Viserio\Component\Contracts\Parsers\Exceptions\ParseException;

class YamlDumper implements DumperContract
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
     *
     * @codeCoverageIgnore
     */
    public function dump(array $data): string
    {
        try {
            return SymfonyYaml::dump($data);
        } catch (InvalidArgumentException $exception) {
            throw new ParseException([
                'message'   => $exception->getMessage(),
                'exception' => $exception,
            ]);
        }
    }
}
