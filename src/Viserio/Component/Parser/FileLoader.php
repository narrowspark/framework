<?php
declare(strict_types=1);
namespace Viserio\Component\Parser;

use Viserio\Component\Contract\Parser\Exception\FileNotFoundException;
use Viserio\Component\Contract\Parser\Exception\NotSupportedException;
use Viserio\Component\Contract\Parser\Loader as LoaderContract;

class FileLoader implements LoaderContract
{
    private const TAG_PARSER = TaggableParser::class;

    private const GROUP_PARSER = GroupParser::class;

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
    public function getDirectories(): array
    {
        return $this->directories;
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
    public function addDirectory(string $directory): LoaderContract
    {
        if (! \in_array($directory, $this->directories, true)) {
            $this->directories[] = $directory;
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function load(string $file, array $options = null): array
    {
        $this->checkOption($options);

        $parser = $this->getParser($options);

        // Set the right Parser for data and return data array
        return $parser->parse($this->exists($file));
    }

    /**
     * {@inheritdoc}
     */
    public function exists(string $file): string
    {
        $key = \str_replace(\DIRECTORY_SEPARATOR, '', $file);

        if (isset($this->exists[$key])) {
            return $this->exists[$key];
        }

        $file = $this->getPath($file) . $file;

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
            $dirFile = $directory . \DIRECTORY_SEPARATOR . $file;

            if (\file_exists($dirFile)) {
                return $directory . \DIRECTORY_SEPARATOR;
            }
        }

        return '';
    }

    /**
     * Check if the right option are given.
     *
     * @param null|array $options
     *
     * @throws \Viserio\Component\Contract\Parser\Exception\NotSupportedException
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
            throw new NotSupportedException('Only the options "tag" and "group" are supported.');
        }
    }

    /**
     * Get the right parser.
     *
     * @param null|array $options
     *
     * @return \Viserio\Component\Parser\Parser
     */
    protected function getParser(?array $options): Parser
    {
        if (($tag = $options['tag'] ?? null) !== null) {
            $class = self::TAG_PARSER;

            return (new $class())->setTag($tag);
        }

        if (($group = $options['group'] ?? null) !== null) {
            $class = self::GROUP_PARSER;

            return (new $class())->setGroup($group);
        }

        return new Parser();
    }
}
