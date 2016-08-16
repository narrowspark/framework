<?php
declare(strict_types=1);
namespace Viserio\Parsers;

use Viserio\Contracts\{
    Parsers\Exception\NotSupportedException,
    Parsers\Format as FormatContract,
    Parsers\Parser as ParserContract
};
use Viserio\Parsers\Formats\{
    BSON,
    Csv,
    INI,
    JSON,
    MSGPack,
    PHP,
    Po,
    QueryStr,
    Serialize,
    TOML,
    XML,
    YAML
};
use Viserio\Support\Traits\NormalizePathAndDirectorySeparatorTrait;

class Parser implements ParserContract
{
    use NormalizePathAndDirectorySeparatorTrait;

    /**
     * @var array Supported Formats
     */
    private $supportedFormats = [
        // XML
        'application/xml'                   => 'xml',
        'text/xml'                          => 'xml',
        // JSON
        'application/json'                  => 'json',
        'application/x-javascript'          => 'json',
        'text/javascript'                   => 'json',
        'text/x-javascript'                 => 'json',
        'text/x-json'                       => 'json',
        // BSON
        'application/bson'                  => 'bson',
        // MSGPACK
        'application/msgpack'               => 'msgpack',
        'application/x-msgpack'             => 'msgpack',
        // YAML
        'text/yaml'                         => 'yaml',
        'text/x-yaml'                       => 'yaml',
        'application/yaml'                  => 'yaml',
        'application/x-yaml'                => 'yaml',
        // MISC
        'application/vnd.php.serialized'    => 'serialize',
        'application/x-www-form-urlencoded' => 'querystr',
    ];

    private $supportedFileFormats = [
        'bson',
        'csv',
        'ini',
        'json',
        'php',
        'po',
        'toml',
        'xml',
        'yaml',
    ];

    private $supportedParsers = [
        'bson' => BSON::class,
        'csv' => Csv::class,
        'ini' => INI::class,
        'json' => JSON::class,
        'msgpack' => MSGPack::class,
        'php' => PHP::class,
        'po' => Po::class,
        'querystr' => QueryStr::class,
        'serialize' => Serialize::class,
        'toml' => TOML::class,
        'xml' => XML::class,
        'yaml' => YAML::class,
    ];

    /**
     * {@inheritdoc}
     */
    public function getFormat($format = null): string
    {
        if ($format !== null) {
            $format = strtolower($format);
        } else {
            $format = '';
        }

        $format = self::normalizeDirectorySeparator($format);

        if (is_file($format)) {
            return pathinfo($format, PATHINFO_EXTENSION);
        }

        return $_SERVER['HTTP_CONTENT_TYPE'] ?? $format;
    }

    /**
     * {@inheritdoc}
     */
    public function parse(string $payload): array
    {
        if (! $payload) {
            return [];
        }

        $format = $this->getFormat($payload);

        if ($format !== 'php') {
            $payload = self::normalizeDirectorySeparator($payload);

            if (is_file($payload)) {
                $payload = file_get_contents($payload);
            }
        }

        return $this->getParser($format)->parse($payload);
    }

    /**
     * {@inheritdoc}
     */
    public function getParser(string $type): FormatContract
    {
        $supportedFileFormats = array_flip($this->supportedFileFormats);

        if (isset($supportedFileFormats[$type])) {
            return new $this->supportedParsers[$type]();
        } elseif (isset($this->supportedFormats[$type])) {
            return new $this->supportedParsers[$this->supportedFormats[$type]]();
        }

        throw new NotSupportedException(sprintf('Format [%s] from string/file is not supported.', $type));
    }
}
