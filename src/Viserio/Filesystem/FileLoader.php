<?php
namespace Viserio\Filesystem;

use Viserio\Contracts\Filesystem\Loader as LoaderContract;
use Viserio\Contracts\Filesystem\Parser as ParserContract;
use Viserio\Contracts\Filesystem\UnsupportedFormatException;
use Viserio\Filesystem\Parser\Ini as IniParser;
use Viserio\Filesystem\Parser\Json as JsonParser;
use Viserio\Filesystem\Parser\Php as PhpParser;
use Viserio\Filesystem\Parser\Toml as TomlParser;
use Viserio\Filesystem\Parser\Xml as XmlParser;
use Viserio\Filesystem\Parser\Yaml as YamlParser;

class FileLoader implements LoaderContract
{
    /**
     * The filesystem instance.
     *
     * @var \Viserio\Filesystem\Filesystem
     */
    protected $files;

    /**
     * The default data path.
     *
     * @var string
     */
    protected $defaultPath;

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
        'php'  => PhpParser::class,
        'ini'  => IniParser::class,
        'xml'  => XmlParser::class,
        'json' => JsonParser::class,
        'yaml' => YamlParser::class,
        'toml' => TomlParser::class,
    ];

    /**
     * Create a new file data loader.
     *
     * @param Filesystem $files
     * @param string     $defaultPath
     */
    public function __construct(Filesystem $files, $defaultPath)
    {
        $this->files       = $files;
        $this->defaultPath = $defaultPath;
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
        $path = $this->getPath($namespace);

        // Determine if the given file exists.
        $this->exists($file, $group, $environment, $namespace);

        // Get checked data file
        $dataFile = $this->exists[preg_replace('[/]', '', $namespace . $group . $file)];

        // Set the right Parser for data
        $parser   = $this->parser($this->files->extension($file), $dataFile);

        // return data array
        $items    = $parser->load($dataFile, $group);

        // Finally we're ready to check for the environment specific data
        // file which will be merged on top of the main arrays so that they get
        // precedence over them if we are currently in an environments setup.
        $env         = sprintf('/%s/%s', $environment, $file);

        // Get checked env data file
        $envdataFile = $this->exists[preg_replace('[/]', '', $namespace . $environment . $group . $file)];

        if ($this->files->exists($envdataFile)) {
            // Set the right parser for environment data
            $envParser = $this->parser($this->files->extension($file), $path . $env);

            // Return data array
            $envItems  = $envParser->load($envdataFile, $group);

            // Merege env data and data
            $items = $this->dataMerge($items, $envItems);
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
     * @return bool|array
     */
    public function exists($file, $group = null, $environment = null, $namespace = null)
    {
        $envKey = $namespace . $environment . $group . $file;
        $envKey = preg_replace('[/]', '', $envKey);

        $key = $namespace . $group . $file;
        $key = preg_replace('[/]', '', $key);

        // We'll first check to see if we have determined if this namespace and
        // group combination have been checked before. If they have, we will
        // just return the cached result so we don't have to hit the disk.

        if (isset($this->exists[$envKey]) || isset($this->exists[$key])) {
            return $this->exists;
        }

        $path = $this->getPath($namespace);

        // To check if a group exists, we will simply get the path based on the
        // namespace, and then check to see if this files exists within that
        // namespace. False is returned if no path exists for a namespace.
        if ($path . $file === null) {
            return $this->exists[$key] = false;
        }

        if ($path . $environment . $file === null) {
            $this->exists[$envKey] = false;
        }

        // Finally, we can simply check if this file exists. We will also cache
        // the value in an array so we don't have to go through this process
        // again on subsequent checks for the existing of the data file.
        $file    = sprintf('%s/%s', $path, $file);

        $envFile = sprintf('%s/%s/%s', $path, $environment, $file);

        if ($this->files->exists($envFile)) {
            $this->exists[$envKey] = $envFile;
        }

        return $this->exists[$key] = $file;
    }

    /**
     * Apply any cascades to an array of package options.
     *
     * @param string      $file
     * @param string|null $packages
     * @param string|null $group
     * @param string|null $env
     * @param array|null  $items
     * @param string      $namespace
     *
     * @return array|null
     */
    public function cascadePackage(
        $file,
        $packages = null,
        $group = null,
        $env = null,
        $items = null,
        $namespace = 'packages'
    ) {
        // First we will look for a data file in the packages data
        // folder. If it exists, we will load it and merge it with these original
        // options so that we will easily 'cascade' a package's datas.
        if ($this->exists($file, sprintf('%s/%s/%s', $namespace, $packages, $env), null, $group)) {
            $items = $this->dataMerge(
                $items,
                $this->files->get(
                    $this->exists[preg_replace('[/]', '', $namespace . $packages . $env . $group . $file)]
                )
            );
        }

        // Once we have merged the regular package data we need to look for
        // an environment specific data file. If one exists, we will get
        // the contents and merge them on top of this array of options we have.
        $path = $this->getPackagePath($env, $packages, $group, $file, $namespace);

        if ($this->exists($path)) {
            $items = $this->dataMerge($items, $this->files->get($path));
        }

        return $items;
    }

    /**
     * Get the package path for an environment and group.
     *
     * @param string      $env
     * @param string      $package
     * @param string      $group
     * @param string|null $namespace
     * @param string      $file
     *
     * @return string
     */
    protected function getPackagePath($env, $package, $group, $file, $namespace = null)
    {
        $file = sprintf('packages/%s/%s/%s/%s', $package, $env, $group, $file);
        $file = preg_replace('[//]', '/', $file);

        return $this->getPath($namespace) . $file;
    }

    /**
     * Get the data path for a namespace.
     *
     * @param string $namespace
     *
     * @return string
     */
    protected function getPath($namespace)
    {
        if ($namespace === null) {
            return $this->defaultPath;
        } elseif (isset($this->hints[$namespace])) {
            return $this->hints[$namespace];
        }

        return '';
    }

    /**
     * Add a new namespace to the loader.
     *
     * @param string $namespace
     * @param string $hint
     */
    public function addNamespace($namespace, $hint)
    {
        $this->hints[$namespace] = $hint;
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
     */
    public function addParser($format, ParserContract $parser)
    {
        $this->parser[$format] = $parser;
    }

    /**
     * Obtains the list of supported formats.
     *
     * @return array
     */
    public function getFormats()
    {
        return array_keys($this->parser);
    }

    /**
     * Sensibly merge data arrays.
     *
     * @param dynamic array
     *
     * @return string
     */
    protected function dataMerge()
    {
        $result = [];

        foreach (func_get_args() as $arg) {
            foreach ($arg as $key => $value) {
                if (is_numeric($key)) {
                    $result[] = $value;
                } elseif (array_key_exists($key, $result) && is_array($result[$key]) && is_array($value)) {
                    $result[$key] = $this->dataMerge($result[$key], $value);
                } else {
                    $result[$key] = $value;
                }
            }
        }

        return (array) $result;
    }

    /**
     * Get a file's contents by requiring it.
     *
     * @param string $path
     *
     * @return string|null
     */
    protected function getRequire($path)
    {
        return $this->files->getRequire($path);
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
     * @param string $ext  file extension
     * @param string $path file path
     *
     * @throws \Viserio\Contracts\Filesystem\UnsupportedFormatException
     *
     * @return object
     */
    protected function parser($ext, $path)
    {
        if (isset($this->parser[$ext])) {
            $class  = $this->parser[$ext];
            $parser = new $class($this->getFilesystem());

            if ($parser->supports($path)) {
                return $parser;
            }
        }

        throw new UnsupportedFormatException(
            sprintf('Unable to find the right Parser for [%s]', $ext)
        );
    }
}
