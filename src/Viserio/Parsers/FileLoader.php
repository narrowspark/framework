<?php
namespace Viserio\Parsers;

use Narrowspark\Arr\StaticArr as Arr;
use Viserio\Contracts\Parsers\Loader as LoaderContract;
use Viserio\Contracts\Parsers\TaggableParser as TaggableParserContract;
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
     * The filesystem instance.
     *
     * @var \Viserio\Filesystem\Filesystem
     */
    private $filesystem;

    /**
     * The parser instance.
     *
     * @var TaggableParserContract
     */
    protected $parser;

    /**
     * All directories to look for a file.
     *
     * @var array
     */
    protected $directories;

    /**
     * A cache of whether namespaces and groups exists.
     *
     * @var array
     */
    protected $exists = [];

    /**
     * Create a new fileloader.
     *
     * @param TaggableParserContract $parser
     * @param array                  $directories
     */
    public function __construct(TaggableParserContract $parser, array $directories)
    {
        $this->parser      = $parser;
        $this->filesystem  = $parser->getFilesystem();
        $this->directories = $directories;
    }

    /**
     * Get parser.
     *
     * @return TaggableParserContract
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
     * @return self
     */
    public function setDirectories(array $directories)
    {
        foreach ($directories as $directory) {
            $this->addDirectory($directory);
        }

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
            $this->directories[] = $this->normalizeDirectorySeparator($directory);
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function load($file, $tag = null)
    {
        // Determine if the given file exists.
        $path  = $this->exists($file);

        $parser = $this->parser;

        if ($tag !== null) {
            $parser->setTag($tag);
        }

        // Set the right Parser for data and return data array
        return $parser->parse($path);
    }

    /**
     * {@inheritdoc}
     */
    public function exists($file)
    {
        $key = str_replace('/', '', $file);

        // Finally, we can simply check if this file exists. We will also cache
        // the value in an array so we don't have to go through this process
        // again on subsequent checks for the existing of the data file.
        $path = $this->getPath($file);
        $file = $this->normalizeDirectorySeparator($path . $file);

        if ($this->filesystem->has($file)) {
            return $this->exists[$key] = $file;
        }

        // False is returned if no path exists for a namespace.
        $this->exists[$key] = false;

        return false;
    }

    /**
     * Get the data path for a file.
     *
     * @param string $file
     *
     * @return string
     */
    protected function getPath($file)
    {
        foreach ($this->directories as $directory) {
            $dirFile = $this->normalizeDirectorySeparator($directory . '/' . $file);

            if ($this->filesystem->has($dirFile)) {
                return $this->normalizeDirectorySeparator($directory) . '/';
            }
        }

        return '';
    }
}
