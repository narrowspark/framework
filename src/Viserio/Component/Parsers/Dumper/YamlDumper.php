<?php
declare(strict_types=1);
namespace Viserio\Component\Parsers\Dumper;

use InvalidArgumentException;
use RuntimeException;
use Symfony\Component\Yaml\Yaml as SymfonyYaml;
use Viserio\Component\Contract\Parsers\Dumper as DumperContract;
use Viserio\Component\Contract\Parsers\Exception\ParseException;

class YamlDumper implements DumperContract
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
