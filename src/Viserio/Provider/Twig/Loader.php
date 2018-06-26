<?php
declare(strict_types=1);
namespace Viserio\Provider\Twig;

use InvalidArgumentException;
use Twig\Error\LoaderError;
use Twig\Loader\ExistsLoaderInterface;
use Twig\Loader\LoaderInterface;
use Twig\Source;
use Viserio\Component\Contract\Filesystem\Exception\FileNotFoundException;
use Viserio\Component\Contract\View\Finder as FinderContract;

class Loader implements LoaderInterface, ExistsLoaderInterface
{
    /**
     * The filesystem instance.
     *
     * @var \Viserio\Component\Contract\Filesystem\Filesystem
     */
    protected $files;

    /**
     * The finder instance.
     *
     * @var \Viserio\Component\Contract\View\Finder
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
     * @param \Viserio\Component\Contract\View\Finder $finder
     */
    public function __construct(FinderContract $finder)
    {
        $this->files  = $finder->getFilesystem();
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
    public function getSourceContext($name)
    {
        $template = $this->findTemplate($name);

        try {
            $source = $this->files->read($template);
        } catch (FileNotFoundException $exception) {
            throw new LoaderError(\sprintf('Twig file [%s] was not found.', $exception->getMessage()));
        }

        if ($source === false) {
            throw new LoaderError(\sprintf('A error occurred during template [%s] reading', $name));
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
        return $this->files->getTimestamp($this->findTemplate($name)) <= $time;
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
        if ($this->files->getExtension($name) === $this->extension) {
            $name = \mb_substr($name, 0, -((int) \mb_strlen($this->extension) + 1));
        }

        return $name;
    }
}
