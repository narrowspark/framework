<?php

declare(strict_types=1);

/**
 * This file is part of Narrowspark Framework.
 *
 * (c) Daniel Bannert <d.bannert@anolilab.de>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Viserio\Component\Parser;

use Viserio\Contract\Parser\Exception\FileNotFoundException;
use Viserio\Contract\Parser\Exception\NotSupportedException;
use Viserio\Contract\Parser\Loader as LoaderContract;

class FileLoader implements LoaderContract
{
    /** @var string */
    private const TAG_PARSER = TaggableParser::class;

    /** @var string */
    private const GROUP_PARSER = GroupParser::class;

    /**
     * All directories to look for a file.
     *
     * @var array<int|string, string>
     */
    protected $directories = [];

    /**
     * A cache of whether namespaces and groups exists.
     *
     * @var array<string, string>
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
    public function exists(string $file): string
    {
        $key = \str_replace(\DIRECTORY_SEPARATOR, '', $file);

        if (\array_key_exists($key, $this->exists)) {
            return $this->exists[$key];
        }

        $file = $this->getPath($file) . $file;

        if (\file_exists($file)) {
            return $this->exists[$key] = $file;
        }

        throw new FileNotFoundException(\sprintf('File [%s] not found.', $file));
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
    public function load(string $file, ?array $options = null): array
    {
        if (\is_array($options)) {
            $this->checkOption($options);
        }

        $parser = $this->getParser($options);

        // Set the right Parser for data and return data array
        return $parser->parse($this->exists($file));
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
     * @param array<string, string> $options
     *
     * @throws \Viserio\Contract\Parser\Exception\NotSupportedException
     *
     * @return void
     */
    protected function checkOption(array $options): void
    {
        if (\array_key_exists('tag', $options)) {
            return;
        }

        if (\array_key_exists('group', $options)) {
            return;
        }

        throw new NotSupportedException('Only the options [tag] and [group] are supported.');
    }

    /**
     * Get the right parser.
     *
     * @param null|array<string, string> $options
     *
     * @return \Viserio\Component\Parser\Parser
     */
    protected function getParser(?array $options): Parser
    {
        if (\is_array($options)) {
            if (\array_key_exists('tag', $options)) {
                $class = self::TAG_PARSER;

                return (new $class())->setTag($options['tag']);
            }

            if (\array_key_exists('group', $options)) {
                $class = self::GROUP_PARSER;

                return (new $class())->setGroup($options['group']);
            }
        }

        return new Parser();
    }
}
