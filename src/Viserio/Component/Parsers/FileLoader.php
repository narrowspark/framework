<?php
declare(strict_types=1);
namespace Viserio\Component\Parsers;

use Viserio\Component\Contracts\Parsers\Exception\RuntimeException;
use Viserio\Component\Contracts\Parsers\Exception\FileNotFoundException;
use Viserio\Component\Contracts\Parsers\Loader as LoaderContract;
use Viserio\Component\Support\Traits\NormalizePathAndDirectorySeparatorTrait;

class FileLoader implements LoaderContract
{
    use NormalizePathAndDirectorySeparatorTrait;

    /**
     * Parsers list.
     *
     * @var array
     */
    protected $parsers = [
        'group' => GroupParser::class,
        'tag'   => TaggableParser::class,
    ];

    /**
     * All directories to look for a file.
     *
     * @var array
     */
    protected $directories = [];

    /**
     * A cache of whether namespaces and groups exists.
     *
     * @var array
     */
    protected $exists = [];

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
        if (! \in_array($directory, $this->directories, true)) {
            $this->directories[] = self::normalizeDirectorySeparator($directory);
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function load(string $file, array $options = null): array
    {
        $this->checkOption($options);

        // Determine if the given file exists.
        $path = $this->exists($file);

        if (($tag = $options['tag'] ?? null) !== null) {
            $parser = new $this->parsers['tag']();
            $parser->setTag($tag);
        } elseif (($group = $options['group'] ?? null) !== null) {
            $parser = new $this->parsers['group']();
            $parser->setGroup($group);
        } else {
            $parser = new Parser();
        }

        // Set the right Parser for data and return data array
        return $parser->parse($path);
    }

    /**
     * {@inheritdoc}
     */
    public function exists(string $file): string
    {
        $key = \str_replace('/', '', $file);

        // Finally, we can simply check if this file exists. We will also cache
        // the value in an array so we don't have to go through this process
        // again on subsequent checks for the existing of the data file.

        if (isset($this->exists[$key])) {
            return $this->exists[$key];
        }

        $file = self::normalizeDirectorySeparator($this->getPath($file) . $file);

        if (\file_exists($file)) {
            return $this->exists[$key] = $file;
        }

        throw new FileNotFoundException(\sprintf('File [%s] not found.', $file));
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

            if (\file_exists($dirFile)) {
                return self::normalizeDirectorySeparator($directory) . '/';
            }
        }

        return '';
    }

    /**
     * Check if the right option are given.
     *
     * @param null|array $options
     *
     * @throws \Viserio\Component\Contracts\Parsers\Exception\RuntimeException
     *
     * @return void
     */
    protected function checkOption(?array $options): void
    {
        if (isset($options['tag'])) {
            return;
        }

        if (isset($options['group'])) {
            return;
        }

        if ($options !== null) {
            throw new RuntimeException('Only the options "tag" or "group" is supported.');
        }
    }
}
