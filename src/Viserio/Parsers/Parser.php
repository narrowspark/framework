<?php
namespace Viserio\Parsers;

class Parser
{
    /**
     * The filesystem instance.
     *
     * @var \Viserio\Filesystem\Filesystem
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
        'application/x-www-form-urlencoded' => 'querystr'
    ];

    private $supportedFileFormats = [
        // JSON
        '' => 'json',
        // PHP
        '' => 'php',
        // YAML
        '' => 'yaml',
        // TOML
        '' => 'toml',
    ];

    /**
     * Add filesystem.
     *
     * @param \Viserio\Contracts\Filesystem\Filesystem $files
     */
    public function setFilesystem(Filesystem $filesystem)
    {
        $this->filesystem = $filesystem;

        return $this;
    }

    /**
     * Get filesystem.
     *
     * @return \Viserio\Contracts\Filesystem\Filesystem $files
     */
    public function getFilesystem()
    {
        return $this->filesystem;
    }

    /**
     * Autodetect the payload data type using content-type value.
     *
     * @return string Return the short format code (xml, json, ...).
     */
    public function getFormat($format)
    {
        return '';
    }
}
