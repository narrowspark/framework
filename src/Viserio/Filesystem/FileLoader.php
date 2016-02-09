<?php
namespace Viserio\Filesystem;

use Narrowspark\Arr\StaticArr as Arr;
use Viserio\Contracts\Filesystem\Exception\UnsupportedFormatException;
use Viserio\Contracts\Filesystem\Filesystem as FilesystemContract;
use Viserio\Contracts\Filesystem\Loader as LoaderContract;
use Viserio\Contracts\Filesystem\Parser as ParserContract;
use Viserio\Filesystem\Parsers\IniParser;
use Viserio\Filesystem\Parsers\JsonParser;
use Viserio\Filesystem\Parsers\PhpParser;
use Viserio\Filesystem\Parsers\TomlParser;
use Viserio\Filesystem\Parsers\XmlParser;
use Viserio\Filesystem\Parsers\YamlParser;
use Viserio\Support\Traits\DirectorySeparatorTrait;

class FileLoader implements LoaderContract
{
    use DirectorySeparatorTrait;

    /**
     * The filesystem instance.
     *
     * @var FilesystemContract
     */
    protected $files;

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
     * All parser.
     *
     * @var array
     */
    protected $parser = [
        'ini'  => IniParser::class,
        'json' => JsonParser::class,
        'php'  => PhpParser::class,
        'toml' => TomlParser::class,
        'xml'  => XmlParser::class,
        'yaml' => YamlParser::class,
    ];

    /**
     * Create a new fileloader.
     *
     * @param FilesystemContract $files
     * @param array              $directories
     */
    public function __construct(FilesystemContract $files, array $directories)
    {
        $this->files       = $files;
        $this->directories = $directories;
    }

    /**
     * Get directories
     *
     * @return array
     */
    public function getDirectories()
    {
        return $this->directories;
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
     * Add directory
     *
     * @param string $directory
     *
     * @return FileLoader
     */
    public function addDirectory($directory)
    {
        if (!in_array($directory, $this->directories)) {
            $this->directories[] = $directory;
        }

        return $this;
    }

    /**
     * Load the given data group.
     *
     * @param string      $file
     * @param string|null $group
     * @param string|null $environment
     * @param string|null $namespace
     *
     * @return array
     */
    public function load($file, $group = null, $environment = null, $namespace = null)
    {
        // Determine if the given file exists.
        $dataFile = $this->exists($file, $group, $environment, $namespace);

        // Set the right Parser for data and return data array
        $items    = $this->parser($file)->parse($dataFile, $group);

        if ($envItems = $this->getEnvFileData($file, $group, $environment, $namespace)) {
            // Merege env data and data
            return Arr::merge($items, $envItems);
        }

        return $items;
    }

    /**
     * Determine if the given file exists.
     *
     * @param string      $file
     * @param string|null $group
     * @param string|null $environment
     * @param string|null $namespace
     *
     * @return bool|string
     */
    public function exists($file, $group = null, $environment = null, $namespace = null)
    {
        $key    = str_replace('/', '', $namespace . $group . $file);
        $envKey = str_replace('/', '', $namespace . $environment . $group . $file);

        // We'll first check to see if we have determined if this namespace and
        // group combination have been checked before. If they have, we will
        // just return the cached result so we don't have to hit the disk.
        if (isset($this->exists[$key]) && $environment === null) {
            return $this->exists[$key];
        } elseif (isset($this->exists[$envKey])) {
            return $this->exists[$envKey];
        }

        $path    = $this->getPath($namespace, $file);

        // Finally, we can simply check if this file exists. We will also cache
        // the value in an array so we don't have to go through this process
        // again on subsequent checks for the existing of the data file.
        $envFile = $this->getDirectorySeparator(
            str_replace('//', '/', sprintf('%s/%s/%s', $path, $environment, $file))
        );
        $file    = $this->getDirectorySeparator(
            str_replace('//', '/', sprintf('%s/%s', $path, $file))
        );

        // To check if a group exists, we will simply get the path based on the
        // namespace, and then check to see if this files exists within that namespace.
        if ($this->files->has($envFile) && $environment !== null) {
            return $this->exists[$envKey] = $envFile;
        } elseif ($this->files->has($file)) {
            return $this->exists[$key]    = $file;
        }

        // False is returned if no path exists for a namespace.
        $this->exists[$key]    = false;
        $this->exists[$envKey] = false;

        return false;
    }

    /**
     * Apply any cascades to an array of package options.
     *
     * @param string      $file
     * @param string|null $packages
     * @param string|null $group
     * @param string|null $environment
     * @param array       $items
     * @param string      $namespace
     *
     * @return array|null
     */
    public function cascadePackage(
        $file,
        $packages = null,
        $group = null,
        $environment = null,
        $items = [],
        $namespace = 'packages'
    ) {
        // First we will look for a data file in the packages data
        // folder. If it exists, we will load it and merge it with these original
        // options so that we will easily 'cascade' a package's datas.
        if ($data = $this->exists($file, $group, sprintf('%s/%s', $packages, $environment), $namespace)) {
            $items = Arr::merge($items, $data);
        }

        // Once we have merged the regular package data we need to look for
        // an environment specific data file. If one exists, we will get
        // the contents and merge them on top of this array of options we have.
        $path = $this->getPackagePath($environment, $packages, $group, $file, $namespace);

        if ($data = $this->exists($path)) {
            $items = Arr::merge($items, $data);
        }

        return $items;
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
     * Adds a parser to the fileloader.
     *
     * @param string         $format The format of the parser
     * @param ParserContract $parser The parser
     *
     * @return self
     */
    public function addParser($format, ParserContract $parser)
    {
        $this->parser[$format] = $parser;

        return $this;
    }

    /**
     * Obtains the list of supported formats.
     *
     * @return array
     */
    public function getParsers()
    {
        return array_keys($this->parser);
    }

    /**
     * Get the Filesystem instance.
     *
     * @return \Viserio\Filesystem\Filesystem
     */
    public function getFilesystem()
    {
        return $this->files;
    }

    /**
     * Get the right Parser for data file.
     *
     * @param string $ext file extension
     *
     * @throws \Viserio\Contracts\Filesystem\Exception\UnsupportedFormatException
     *
     * @return object
     */
    protected function parser($ext)
    {
        $ext = $this->files->extension($ext);

        if (isset($this->parser[$ext])) {
            return new $this->parser[$ext]($this->getFilesystem());
        }

        throw new UnsupportedFormatException(
            sprintf('Unable to find the right Parser for [%s].', $ext)
        );
    }

    /**
     * Get the package path for an environment and group.
     *
     * @param string      $environment
     * @param string      $package
     * @param string      $group
     * @param string|null $namespace
     * @param string      $file
     *
     * @return string
     */
    protected function getPackagePath($environment, $package, $group, $file, $namespace = null)
    {
        $file = sprintf('packages/%s/%s/%s/%s', $package, $environment, $group, $file);

        return $this->getDirectorySeparator($this->getPath($namespace, $file) . '/' . $file);
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
            return $this->getDirectorySeparator($this->hints[$namespace]);
        }

        foreach ($this->directories as $directory) {
            $file = $this->getDirectorySeparator($directory . '/' . $file);

            if ($this->files->has($file)) {
                return $this->getDirectorySeparator($directory);
            }
        }
    }

    /**
     * @param string      $file
     * @param string|null $group
     * @param string|null $environment
     * @param string|null $namespace
     *
     * @return array|null
     */
    protected function getEnvFileData($file, $group = null, $environment = null, $namespace = null)
    {
        if ($environment === null) {
            return;
        }

        // Get checked env data file
        $envFileName = str_replace('/', '', $namespace . $environment . $group . $file);

        // Finally we're ready to check for the environment specific data
        // file which will be merged on top of the main arrays so that they get
        // precedence over them if we are currently in an environments setup.
        $envFile     = str_replace('//', '/', sprintf('/%s/%s', $environment, $file));
        $path        = $this->getPath($namespace, $envFile);

        if (isset($this->exists[$envFileName])) {
            $envFilePath = $this->exists[$envFileName];

            if ($this->files->exists($envFilePath)) {
                // Set the right parser for environment data and return data array
                return $this->parser($envFilePath)->parse($envFilePath, $group);
            }
        }

        return;
    }
}
