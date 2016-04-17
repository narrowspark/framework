<?php
namespace Viserio\Parsers\Formats;

use League\Flysystem\FileNotFoundException;
use RuntimeException;
use Symfony\Component\Yaml\Exception\ParseException as YamlParseException;
use Symfony\Component\Yaml\Parser;
use Viserio\Contracts\Parsers\Exception\ParseException;
use Viserio\Contracts\Parsers\Format as FormatContract;

class YAML implements FormatContract
{
    /**
     * The filesystem instance.
     *
     * @var \Symfony\Component\Yaml\Parser
     */
    protected $parser;

    /**
     * Create a new Yaml parser.
     */
    public function __construct()
    {
        if (!class_exists('Symfony\\Component\\Yaml\\Yaml')) {
            throw new RuntimeException('Unable to read yaml as the Symfony Yaml Component is not installed.');
        }

        $this->parser = new Parser();
    }

    /**
     * {@inheritdoc}
     */
    public function parse($payload)
    {
        try {
            return $this->parser->parse(
                trim(preg_replace('/\t+/', '', $payload))
            );
        } catch (YamlParseException $exception) {
            throw new ParseException([
                'message' => 'Unable to parse the YAML string',
                'line' => $exception->getMessage(),
            ]);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function dump(array $data)
    {
        return $this->parser->dump($data);
    }
}
