<?php
namespace Viserio\Parsers;

use Viserio\Contracts\Parsers\{
    Loader as LoaderContract,
    TaggableParser as TaggableParserContract
};
use Viserio\Support\Traits\NormalizePathAndDirectorySeparatorTrait;

class FileLoader implements LoaderContract
{
    use NormalizePathAndDirectorySeparatorTrait;

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
     * The filesystem instance.
     *
     * @var \Viserio\Filesystem\Filesystem
     */
    private $filesystem;

    /**
     * Create a new fileloader.
     *
     * @param \Viserio\Contracts\Parsers\TaggableParser $parser
     * @param array                  $directories
     */
    public function __construct(TaggableParserContract $parser, array $directories)
    {
        $this->parser = $parser;
        $this->filesystem = $parser->getFilesystem();
        $this->directories = $directories;
    }

    /**
     * Get parser.
     *
     * @return \Viserio\Contracts\Parsers\TaggableParser
     */
    public function getParser(): TaggableParserContract
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
    public function setDirectories(array $directories): LoaderContract
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
    public function getDirectories(): array
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
    public function addDirectory(string $directory): LoaderContract
    {
        if (! in_array($directory, $this->directories)) {
            $this->directories[] = self::normalizeDirectorySeparator($directory);
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function load(string $file, string $tag = null): array
    {
        // Determine if the given file exists.
        $path = $this->exists($file);

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
    public function exists(string $file)
    {
        $key = str_replace('/', '', $file);

        // Finally, we can simply check if this file exists. We will also cache
        // the value in an array so we don't have to go through this process
        // again on subsequent checks for the existing of the data file.
        $path = $this->getPath($file);
        $file = self::normalizeDirectorySeparator($path . $file);

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
    protected function getPath(string $file): string
    {
        foreach ($this->directories as $directory) {
            $dirFile = self::normalizeDirectorySeparator($directory . '/' . $file);

            if ($this->filesystem->has($dirFile)) {
                return self::normalizeDirectorySeparator($directory) . '/';
            }
        }

        return '';
    }
}
