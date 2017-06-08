<?php
declare(strict_types=1);
namespace Viserio\Component\Parsers;

use finfo;
use RuntimeException;
use Viserio\Component\Contracts\Parsers\Exceptions\NotSupportedException;
use Viserio\Component\Contracts\Parsers\Parser as ParserContract;
use Viserio\Component\Parsers\Parsers\IniParser;
use Viserio\Component\Parsers\Parsers\JsonParser;
use Viserio\Component\Parsers\Parsers\PhpParser;
use Viserio\Component\Parsers\Parsers\PoParser;
use Viserio\Component\Parsers\Parsers\QtParser;
use Viserio\Component\Parsers\Parsers\QueryStrParser;
use Viserio\Component\Parsers\Parsers\SerializeParser;
use Viserio\Component\Parsers\Parsers\TomlParser;
use Viserio\Component\Parsers\Parsers\XliffParser;
use Viserio\Component\Parsers\Parsers\XmlParser;
use Viserio\Component\Parsers\Parsers\YamlParser;
use Viserio\Component\Support\Traits\NormalizePathAndDirectorySeparatorTrait;

class Parser
{
    use NormalizePathAndDirectorySeparatorTrait;

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
        'php'       => PhpParser::class,
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
     * @param \Viserio\Component\Contracts\Parsers\Parser $parser
     * @param string                                      $extension
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
     * @throws \Viserio\Component\Contracts\Parsers\Exceptions\ParseException
     * @throws \RuntimeException                                              if an error occurred during reading
     *
     * @return array
     */
    public function parse(string $payload): array
    {
        if ($payload === '') {
            return [];
        }

        $format = $this->getFormat($payload);

        if ($format !== 'php') {
            $fileName = self::normalizeDirectorySeparator($payload);

            if (is_file($fileName)) {
                $payload  = file_get_contents($fileName);

                if ($payload === false) {
                    throw new RuntimeException(sprintf('A error occurred during reading [%s]', $fileName));
                }
            }
        }

        return $this->getParser($format)->parse($payload);
    }

    /**
     * Get supported parser on extension or mime type.
     *
     * @param string $type
     *
     * @throws \Viserio\Component\Contracts\Parsers\Exception\NotSupportedException
     *
     * @return \Viserio\Component\Contracts\Parsers\Parser
     */
    public function getParser(string $type): ParserContract
    {
        if (isset(self::$supportedParsers[$type])) {
            return new self::$supportedParsers[$type]();
        } elseif (isset(self::$supportedMimeTypes[$type])) {
            $class = self::$supportedParsers[self::$supportedMimeTypes[$type]];

            if (is_object($class)) {
                return $class;
            }

            return new $class();
        }

        throw new NotSupportedException(sprintf('Given extension or mime type [%s] is not supported.', $type));
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

        if (is_file($file = self::normalizeDirectorySeparator($payload))) {
            $format = pathinfo($file, PATHINFO_EXTENSION);
        } elseif (is_string($payload)) {
            // try if content is json
            json_decode($payload);

            if (json_last_error() === JSON_ERROR_NONE) {
                return 'json';
            }

            $format = (new finfo(FILEINFO_MIME_TYPE))->buffer($payload);
        }

        return self::$supportedMimeTypes[$format] ?? $format;
    }
}
