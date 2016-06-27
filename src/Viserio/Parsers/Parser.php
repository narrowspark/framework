<?php
namespace Viserio\Parsers;

use Viserio\Contracts\{
    Filesystem\Filesystem as FilesystemContract,
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

class Parser implements ParserContract
{
    /**
     * The filesystem instance.
     *
     * @var \Viserio\Contracts\Filesystem\Filesystem
     */
    protected $filesystem;

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
     * Add filesystem.
     *
     * @param \Viserio\Contracts\Filesystem\Filesystem $filesystem
     */
    public function __construct(FilesystemContract $filesystem)
    {
        $this->filesystem = $filesystem;
    }

    /**
     * Get filesystem.
     *
     * @return \Viserio\Contracts\Filesystem\Filesystem
     */
    public function getFilesystem(): FilesystemContract
    {
        return $this->filesystem;
    }

    /**
     * Autodetect the payload data type using content-type value.
     *
     * @param string|null $format
     *
     * @return string Return the short format code (xml, json, ...).
     */
    public function getFormat($format = null)
    {
        $format = strtolower($format);
        $fsystem = $this->filesystem;

        if ($fsystem->isFile($format)) {
            return $fsystem->getExtension($format);
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
        $fsystem = $this->filesystem;

        if ($format !== 'php') {
            if ($fsystem->isFile($payload)) {
                $payload = $fsystem->read($payload);
            }
        }

        return $this->getParser($format)->parse($payload);
    }

    /**
     * Get supported parser.
     *
     * @param string $type
     *
     * @throws \Viserio\Contracts\Parsers\Exception\NotSupportedException
     *
     * @return \Viserio\Contracts\Parsers\Format
     */
    public function getParser($type): FormatContract
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
