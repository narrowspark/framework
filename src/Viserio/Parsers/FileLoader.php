<?php
declare(strict_types=1);
namespace Viserio\Parsers;

use Viserio\Contracts\Parsers\Loader as LoaderContract;
use Viserio\Contracts\Parsers\TaggableParser as TaggableParserContract;
use Viserio\Support\Traits\NormalizePathAndDirectorySeparatorTrait;

class FileLoader implements LoaderContract
{
    use NormalizePathAndDirectorySeparatorTrait;

    /**
     * The parser instance.
     *
     * @var \Viserio\Contracts\Parsers\TaggableParser
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
     * @param \Viserio\Contracts\Parsers\TaggableParser $parser
     * @param array                                     $directories
     */
    public function __construct(TaggableParserContract $parser, array $directories)
    {
        $this->parser = $parser;
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
     * {@inheritdoc}
     */
    public function setDirectories(array $directories): LoaderContract
    {
        foreach ($directories as $directory) {
            $this->addDirectory($directory);
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getDirectories(): array
    {
        return $this->directories;
    }

    /**
     * {@inheritdoc}
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

        if (file_exists($file)) {
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

            if (file_exists($dirFile)) {
                return self::normalizeDirectorySeparator($directory) . '/';
            }
        }

        return '';
    }
}
