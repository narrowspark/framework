<?php
namespace Viserio\Filesystem;

use Narrowspark\Arr\StaticArr as Arr;
use Viserio\Contracts\Config\Loader as LoaderContract;
use Viserio\Contracts\Filesystem\Exception\UnsupportedFormatException;
use Viserio\Contracts\Parsers\Parser as ParserContract;
use Viserio\Parsers\IniParser;
use Viserio\Parsers\JsonParser;
use Viserio\Parsers\PHPParser;
use Viserio\Parsers\TomlParser;
use Viserio\Parsers\XmlParser;
use Viserio\Parsers\YamlParser;
use Viserio\Support\Traits\NormalizePathAndDirectorySeparatorTrait;

class FileLoader implements LoaderContract
{
    use NormalizePathAndDirectorySeparatorTrait;

    /**
     * The parser instance.
     *
     * @var ParserContract
     */
    protected $parser;

    /**
     * All directories to look for a file.
     *
     * @var array
     */
    protected $directories;

    /**
     * All of the named path hints.
     *
     * @var array
     */
    protected $hints = [];

    /**
     * A cache of whether namespaces and groups exists.
     *
     * @var array
     */
    protected $exists = [];

    /**
     * Create a new fileloader.
     *
     * @param FilesystemContract $files
     * @param ParserContract     $parser
     * @param array              $directories
     */
    public function __construct(ParserContract $parser, array $directories)
    {
        $this->parser      = $parser;
        $this->directories = $directories;
    }

    /**
     * Get parser.
     *
     * @return array
     */
    public function getParser()
    {
        return $this->parser;
    }

    /**
     * Set directories
     *
     * @param array $directories
     *
     * @return FileLoader
     */
    public function setDirectories(array $directories)
    {
        $this->directories = $directories;

        return $this;
    }

    /**
     * Get directories.
     *
     * @return array
     */
    public function getDirectories()
    {
        return $this->directories;
    }

    /**
     * Add directory.
     *
     * @param string $directory
     *
     * @return self
     */
    public function addDirectory($directory)
    {
        if (!in_array($directory, $this->directories)) {
            $this->directories[] = $directory;
        }

        return $this;
    }

    /**
     * Load the given file path.
     *
     * @param string $file
     *
     * @return array
     */
    public function load($file)
    {
        // Determine if the given file exists.
        $path  = $this->exists($file);

        // Set the right Parser for data and return data array
        return $this->parser->parse($path);
    }

    /**
     * Determine if the given file exists.
     *
     * @param string $file
     *
     * @return bool|string
     */
    public function exists($file)
    {
        $key = str_replace('/', '', $namespace . $file);

        // We'll first check to see if we have determined if this namespace
        // combination have been checked before. If they have, we will
        // just return the cached result so we don't have to hit the disk.
        if (isset($this->exists[$envKey])) {
            return $this->exists[$envKey];
        }

        // Finally, we can simply check if this file exists. We will also cache
        // the value in an array so we don't have to go through this process
        // again on subsequent checks for the existing of the data file.
        $path = $this->getPath($namespace, $file);
        $file = $this->normalizeDirectorySeparator($path.$file);

        if ($this->parser->getFilesystem()->has($file)) {
            return $this->exists[$key] = $file;
        }

        // False is returned if no path exists for a namespace.
        $this->exists[$key] = false;

        return false;
    }

    /**
     * Add a new namespace to the loader.
     *
     * @param string $namespace
     * @param string $hint
     *
     * @return self
     */
    public function addNamespace($namespace, $hint)
    {
        $this->hints[$namespace] = $hint;

        return $this;
    }

    /**
     * Returns all registered namespaces with the data
     * loader.
     *
     * @return array
     */
    public function getNamespaces()
    {
        return $this->hints;
    }

    /**
     * Get the data path for a namespace.
     *
     * @param string $namespace
     * @param string $file
     *
     * @return string
     */
    protected function getPath($namespace, $file)
    {
        if (isset($this->hints[$namespace])) {
            return $this->normalizeDirectorySeparator($this->hints[$namespace]. '/');
        }

        foreach ($this->directories as $directory) {
            $file = $this->normalizeDirectorySeparator($directory . '/' . $file);

            if ($this->parser->getFilesystem()->has($file)) {
                return $this->normalizeDirectorySeparator($directory. '/');
            }
        }

        return '';
    }
}
