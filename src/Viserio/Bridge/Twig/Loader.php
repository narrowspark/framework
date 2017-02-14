<?php
declare(strict_types=1);
namespace Viserio\Bridge\Twig;

use InvalidArgumentException;
use Twig_Source;
use Twig_Error_Loader;
use Twig_ExistsLoaderInterface;
use Twig_LoaderInterface;
use Viserio\Component\Contracts\Filesystem\Filesystem as FilesystemContract;
use Viserio\Component\Contracts\View\Finder as FinderContract;

class Loader implements Twig_LoaderInterface, Twig_ExistsLoaderInterface
{
    /**
     * The filesystem instance.
     *
     * @var \Viserio\Component\Contracts\Filesystem\Filesystem
     */
    protected $files;

    /**
     * The finder instance.
     *
     * @var \Viserio\Component\Contracts\View\Finder
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
     * @param \Viserio\Component\Contracts\Filesystem\Filesystem $files
     * @param \Viserio\Component\Contracts\View\Finder           $finder
     */
    public function __construct(FilesystemContract $files, FinderContract $finder)
    {
        $this->files  = $files;
        $this->finder = $finder;
    }

    /**
     * Set file extension for the twig loader.
     *
     * @param string $extension
     *
     * @codeCoverageIgnore
     */
    public function setExtension(string $extension): Twig_LoaderInterface
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
        } catch (Twig_Error_Loader $exception) {
            return false;
        }

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function getSourceContext($name)
    {
        $template = $this->findTemplate($name);

        return new Twig_Source($this->files->read($template), $name, $template);
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
        if ($this->files->has($name)) {
            return $name;
        }

        $name = $this->normalizeName($name);

        if (isset($this->cache[$name])) {
            return $this->cache[$name];
        }

        try {
            $found              = $this->finder->find($name);
            $this->cache[$name] = $found['path'];
        } catch (InvalidArgumentException $exception) {
            throw new Twig_Error_Loader($exception->getMessage());
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
        if ($this->files->getExtension($name) === $this->extension) {
            $name = mb_substr($name, 0, -(mb_strlen($this->extension) + 1));
        }

        return $name;
    }
}
