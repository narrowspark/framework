<?php

declare(strict_types=1);

/**
 * Copyright (c) 2018-2020 Daniel Bannert
 *
 * For the full copyright and license information, please view
 * the LICENSE.md file that was distributed with this source code.
 *
 * @see https://github.com/narrowspark/automatic
 */

namespace Viserio\Provider\Twig;

use InvalidArgumentException;
use Twig\Error\LoaderError;
use Twig\Loader\LoaderInterface;
use Twig\Source;
use Viserio\Contract\Filesystem\Exception\NotFoundException;
use Viserio\Contract\Filesystem\Filesystem as ContractFilesystem;
use Viserio\Contract\View\Finder as FinderContract;

class Loader implements LoaderInterface
{
    /**
     * The filesystem instance.
     *
     * @var \Viserio\Contract\Filesystem\Filesystem
     */
    protected $filesystem;

    /**
     * The finder instance.
     *
     * @var \Viserio\Contract\View\Finder
     */
    protected $finder;

    /**
     * Twig file extension.
     *
     * @var string
     */
    protected $extension = 'twig';

    /**
     * Template lookup cache.
     *
     * @var array
     */
    protected $cache = [];

    /**
     * Create a new twig loader instance.
     */
    public function __construct(FinderContract $finder, ContractFilesystem $filesystem)
    {
        $this->filesystem = $filesystem;
        $this->finder = $finder;
    }

    /**
     * Set file extension for the twig loader.
     *
     * @codeCoverageIgnore
     */
    public function setExtension(string $extension): LoaderInterface
    {
        $this->extension = $extension;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function exists($name): bool
    {
        try {
            $this->findTemplate($name);
        } catch (LoaderError $exception) {
            return false;
        }

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function getSourceContext($name): Source
    {
        $template = $this->findTemplate($name);

        try {
            $source = $this->filesystem->read($template);
        } catch (NotFoundException $exception) {
            throw new LoaderError(\sprintf('Twig file [%s] was not found.', $exception->getMessage()));
        }

        if ($source === false) {
            throw new LoaderError(\sprintf('A error occurred during template [%s] reading.', $name));
        }

        return new Source($source, $name, $template);
    }

    /**
     * {@inheritdoc}
     *
     * @codeCoverageIgnore
     */
    public function getCacheKey($name): string
    {
        return $this->findTemplate($name);
    }

    /**
     * {@inheritdoc}
     */
    public function isFresh($name, $time): bool
    {
        return $this->filesystem->getLastModified($this->findTemplate($name))->getTimestamp() <= $time;
    }

    /**
     * Return path to template without the need for the extension.
     *
     * @throws \Twig\Error\LoaderError
     */
    public function findTemplate(string $name): string
    {
        if ($this->filesystem->has($name)) {
            return $name;
        }

        $name = $this->normalizeName($name);

        if (isset($this->cache[$name])) {
            return $this->cache[$name];
        }

        try {
            $found = $this->finder->find($name);
            $this->cache[$name] = $found['path'];
        } catch (InvalidArgumentException $exception) {
            throw new LoaderError($exception->getMessage());
        }

        return $this->cache[$name];
    }

    /**
     * Normalize the Twig template name to a name the ViewFinder can use.
     */
    protected function normalizeName(string $name): string
    {
        if ($this->filesystem->getExtension($name) === $this->extension) {
            $name = \substr($name, 0, -(\strlen($this->extension) + 1));
        }

        return $name;
    }
}
