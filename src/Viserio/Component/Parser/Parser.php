<?php
declare(strict_types=1);
namespace Viserio\Component\Parser;

use finfo;
use RuntimeException;
use Viserio\Component\Contract\Parser\Exception\NotSupportedException;
use Viserio\Component\Contract\Parser\Parser as ParserContract;
use Viserio\Component\Parser\Parser\IniParser;
use Viserio\Component\Parser\Parser\JsonParser;
use Viserio\Component\Parser\Parser\PhpArrayParser;
use Viserio\Component\Parser\Parser\PoParser;
use Viserio\Component\Parser\Parser\QtParser;
use Viserio\Component\Parser\Parser\QueryStrParser;
use Viserio\Component\Parser\Parser\SerializeParser;
use Viserio\Component\Parser\Parser\TomlParser;
use Viserio\Component\Parser\Parser\XliffParser;
use Viserio\Component\Parser\Parser\XmlParser;
use Viserio\Component\Parser\Parser\YamlParser;

class Parser
{
    /**
     * Supported mime type formats.
     *
     * @var array
     */
    private static $supportedMimeTypes = [
        // XML
        'application/xml' => 'xml',
        'text/xml'        => 'xml',
        // Xliff
        'application/x-xliff+xml' => 'xlf',
        // JSON
        'application/json'         => 'json',
        'application/x-javascript' => 'json',
        'text/javascript'          => 'json',
        'text/x-javascript'        => 'json',
        'text/x-json'              => 'json',
        // YAML
        'text/yaml'          => 'yaml',
        'text/x-yaml'        => 'yaml',
        'application/yaml'   => 'yaml',
        'application/x-yaml' => 'yaml',
        // MISC
        'application/vnd.php.serialized'    => 'serialize',
        'application/x-www-form-urlencoded' => 'querystr',
    ];

    /**
     * All supported parser.
     *
     * @var array
     */
    private static $supportedParsers = [
        'ini'       => IniParser::class,
        'json'      => JsonParser::class,
        'php'       => PhpArrayParser::class,
        'po'        => PoParser::class,
        'querystr'  => QueryStrParser::class,
        'serialize' => SerializeParser::class,
        'toml'      => TomlParser::class,
        'ts'        => QtParser::class,
        'xml'       => XmlParser::class,
        'xlf'       => XliffParser::class,
        'yaml'      => YamlParser::class,
    ];

    /**
     * Add a new mime type with extension.
     *
     * @param string $mimeType
     * @param string $extension
     *
     * @return void
     */
    public function addMimeType(string $mimeType, string $extension): void
    {
        self::$supportedMimeTypes[$mimeType] = $extension;
    }

    /**
     * Add a new parser.
     *
     * @param \Viserio\Component\Contract\Parser\Parser $parser
     * @param string                                    $extension
     *
     * @return void
     */
    public function addParser(ParserContract $parser, string $extension): void
    {
        self::$supportedParsers[$extension] = $parser;
    }

    /**
     * Parse given file path or content string.
     *
     * @param string $payload
     *
     * @throws \Viserio\Component\Contract\Parser\Exception\ParseException
     * @throws \RuntimeException                                           if an error occurred during reading
     *
     * @return array
     */
    public function parse(string $payload): array
    {
        if ($payload === '') {
            return [];
        }

        $format = $this->getFormat($payload);

        if ($format !== 'php' && \is_file($payload)) {
            $payload = \file_get_contents($payload);

            if ($payload === false) {
                throw new RuntimeException(\sprintf('A error occurred during reading [%s]', $payload));
            }
        }

        return $this->getParser($format)->parse($payload);
    }

    /**
     * Get supported parser on extension or mime type.
     *
     * @param string $type
     *
     * @throws \Viserio\Component\Contract\Parser\Exception\NotSupportedException
     *
     * @return \Viserio\Component\Contract\Parser\Parser
     */
    public function getParser(string $type): ParserContract
    {
        if (isset(self::$supportedParsers[$type])) {
            return new self::$supportedParsers[$type]();
        }

        if (isset(self::$supportedMimeTypes[$type])) {
            $class = self::$supportedParsers[self::$supportedMimeTypes[$type]];

            if (\is_object($class) && $class instanceof ParserContract) {
                return $class;
            }

            return new $class();
        }

        throw new NotSupportedException(\sprintf('Given extension or mime type [%s] is not supported.', $type));
    }

    /**
     * Auto detect the payload data type using finfo and pathinfo.
     *
     * @param string $payload
     *
     * @return string Tries to return the short format code (xml, json, ...) else the mime type
     */
    protected function getFormat(string $payload): string
    {
        $format = '';

        if (\is_file($file = $payload)) {
            $format = \pathinfo($file, \PATHINFO_EXTENSION);
        } elseif (\is_string($payload)) {
            // try if content is json
            \json_decode($payload);

            if (\json_last_error() === \JSON_ERROR_NONE) {
                return 'json';
            }

            $format = (new finfo(\FILEINFO_MIME_TYPE))->buffer($payload);
        }

        return self::$supportedMimeTypes[$format] ?? $format;
    }
}
