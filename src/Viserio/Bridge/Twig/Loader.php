<?php
declare(strict_types=1);
namespace Viserio\Bridge\Twig;

use Twig_LoaderInterface;
use Twig_Error_Loader;
use Twig_ExistsLoaderInterface;
use InvalidArgumentException;
use Viserio\Component\Contracts\Filesystem\Filesystem as FilesystemContract;
use Viserio\Component\Contracts\View\Finder as FinderContract;

class Loader implements Twig_LoaderInterface, Twig_ExistsLoaderInterface
{
    /**
     * @var \Viserio\Component\Contracts\Filesystem\Filesystem
     */
    protected $files;

    /**
     * @var \Viserio\Component\Contracts\View\Finder
     */
    protected $finder;

    /**
     * Twig file extension.
     *
     * @var string
     */
    protected $extension;

    /**
     * Template lookup cache.
     *
     * @var array
     */
    protected $cache = [];

    /**
     * @param \Viserio\Component\Contracts\Filesystem\Filesystem $files
     * @param \Viserio\Component\Contracts\View\Finder           $finder
     * @param string                                             $extension Twig file extension.
     */
    public function __construct(FilesystemContract $files, FinderContract $finder, string $extension = 'twig')
    {
        $this->files     = $files;
        $this->finder    = $finder;
        $this->extension = $extension;
    }

    /**
     * {@inheritdoc}
     */
    public function exists($name): bool
    {
        try {
            $this->findTemplate($name);
        } catch (Twig_Error_Loader $exception) {
            return false;
        }

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function getSource($name)
    {
        return $this->files->get($this->findTemplate($name));
    }

    /**
     * {@inheritdoc}
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
        return $this->files->getTimestamp($this->findTemplate($name)) <= $time;
    }

    /**
     * Return path to template without the need for the extension.
     *
     * @param string $name
     *
     * @throws \Twig_Error_Loader
     *
     * @return string
     */
    public function findTemplate(string $name): string
    {
        if ($this->files->exists($name)) {
            return $name;
        }

        $name = $this->normalizeName($name);

        if (isset($this->cache[$name])) {
            return $this->cache[$name];
        }

        try {
            $this->cache[$name] = $this->finder->find($name);
        } catch (InvalidArgumentException $exception) {
            throw new Twig_Error_Loader($exception->getMessage());
        }

        return $this->cache[$name];
    }

    /**
     * Normalize the Twig template name to a name the ViewFinder can use
     *
     * @param string $name
     *
     * @return string
     */
    protected function normalizeName(string $name): string
    {
        if ($this->files->getExtension($name) === $this->extension) {
            $name = substr($name, 0, - (strlen($this->extension) + 1));
        }
        return $name;
    }
}
