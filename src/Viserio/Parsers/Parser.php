<?php
namespace Viserio\Parsers;

use Viserio\Parsers\Formats\INI;
use Viserio\Parsers\Formats\JSON;
use Viserio\Parsers\Formats\PHP;
use Viserio\Parsers\Formats\QueryStr;
use Viserio\Parsers\Formats\Serialize;
use Viserio\Parsers\Formats\TOML;
use Viserio\Parsers\Formats\XML;
use Viserio\Parsers\Formats\YAML;
use Viserio\Contracts\Parsers\Exception\NotSupportedException;
use Viserio\Contracts\Filesystem\Filesystem;

class Parser
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
        'ini',
        'json',
        'php',
        'toml',
        'xml',
        'yaml',
    ];

    private $supportedParsers = [
        'ini'       => INI::class,
        'json'      => JSON::class,
        'php'       => PHP::class,
        'toml'      => TOML::class,
        'xml'       => XML::class,
        'yaml'      => YAML::class,
        'serialize' => Serialize::class,
        'querystr'  => QueryStr::class,
    ];

    /**
     * Add filesystem.
     *
     * @param \Viserio\Contracts\Filesystem\Filesystem $filesystem
     */
    public function __construct(Filesystem $filesystem)
    {
        $this->filesystem = $filesystem;
    }

    /**
     * Get filesystem.
     *
     * @return \Viserio\Contracts\Filesystem\Filesystem
     */
    public function getFilesystem()
    {
        return $this->filesystem;
    }

    /**
     * Autodetect the payload data type using content-type value.
     *
     * @param string $format
     *
     * @return string Return the short format code (xml, json, ...).
     */
    public function getFormat($format)
    {
        $format  = strtolower($format);
        $fsystem = $this->filesystem;

        if ($fsystem->isFile($format)) {
            return $fsystem->getExtension($format);
        }

        $httpContent = $_SERVER['HTTP_CONTENT_TYPE'];

        return isset($httpContent) ? $httpContent : $format;
    }

    /**
     * Loads a file and output it content as array.
     *
     * @param string $payload
     *
     * @throws \Viserio\Contracts\Parsers\Exception\ParseException
     *
     * @return array|string|null
     */
    public function parse($payload)
    {
        $format  = $this->getFormat($payload);
        $fsystem = $this->filesystem;

        if ($format !== 'php' || $format !== 'xml') {
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
    public function getParser($type)
    {
        $supportedFileFormats = array_flip($this->supportedFileFormats);

        if (isset($supportedFileFormats[$type])) {
            return new $this->supportedParsers[$type];
        } else if (isset($this->supportedFormats[$type])) {
            return new $this->supportedParsers[$this->supportedFormats[$type]];
        }

        throw new NotSupportedException(sprintf('Format [%s] from string/file is not supported.', $type));
    }
}
