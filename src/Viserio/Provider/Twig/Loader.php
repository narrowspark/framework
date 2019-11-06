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

namespace Viserio\Provider\Twig;

use InvalidArgumentException;
use Twig\Error\LoaderError;
use Twig\Loader\LoaderInterface;
use Twig\Source;
use Viserio\Contract\Filesystem\Exception\FileNotFoundException;
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
     *
     * @param \Viserio\Contract\View\Finder           $finder
     * @param \Viserio\Contract\Filesystem\Filesystem $filesystem
     */
    public function __construct(FinderContract $finder, ContractFilesystem $filesystem)
    {
        $this->filesystem = $filesystem;
        $this->finder = $finder;
    }

    /**
     * Set file extension for the twig loader.
     *
     * @param string $extension
     *
     * @return \Twig\Loader\LoaderInterface
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
        } catch (FileNotFoundException $exception) {
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
        return $this->filesystem->getTimestamp($this->findTemplate($name)) <= $time;
    }

    /**
     * Return path to template without the need for the extension.
     *
     * @param string $name
     *
     * @throws \Twig\Error\LoaderError
     *
     * @return string
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
     *
     * @param string $name
     *
     * @return string
     */
    protected function normalizeName(string $name): string
    {
        if ($this->filesystem->getExtension($name) === $this->extension) {
            $name = \substr($name, 0, -(\strlen($this->extension) + 1));
        }

        return $name;
    }
}
