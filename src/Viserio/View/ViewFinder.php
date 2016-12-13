<?php
declare(strict_types=1);
namespace Viserio\View;

use InvalidArgumentException;
use Viserio\Contracts\Filesystem\Filesystem as FilesystemContract;
use Viserio\Contracts\View\Finder as FinderContract;
use Viserio\Support\Traits\NormalizePathAndDirectorySeparatorTrait;

class ViewFinder implements FinderContract
{
    use NormalizePathAndDirectorySeparatorTrait;

    /**
     * The filesystem instance.
     *
     * @var \Viserio\Contracts\Filesystem\Filesystem
     */
    protected $files;

    /**
     * The array of active view paths.
     *
     * @var array
     */
    protected $paths = [];

    /**
     * The array of views that have been located.
     *
     * @var array
     */
    protected $views = [];

    /**
     * The namespace to file path hints.
     *
     * @var array
     */
    protected $hints = [];

    /**
     * Register a view extension with the finder.
     *
     * @var array
     */
    protected $extensions = [
        'php',
        'css',
    ];

    /**
     * Create a new file view loader instance.
     *
     * @param \Viserio\Contracts\Filesystem\Filesystem $files
     * @param array                                    $paths
     * @param null|array                               $extensions
     */
    public function __construct(FilesystemContract $files, array $paths, array $extensions = null)
    {
        $this->files = $files;
        $this->paths = $paths;

        if ($extensions !== null) {
            $this->extensions = array_merge($this->extensions, $extensions);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function find(string $name): array
    {
        if (isset($this->views[$name])) {
            return $this->views[$name];
        }

        if ($this->hasHintInformation($name = trim($name))) {
            return $this->views[$name] = $this->findNamedPathView($name);
        }

        return $this->views[$name] = $this->findInPaths($name, $this->paths);
    }

    /**
     * {@inheritdoc}
     */
    public function addLocation(string $location): FinderContract
    {
        $this->paths[] = $location;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function prependLocation(string $location)
    {
        array_unshift($this->paths, $location);
    }

    /**
     * {@inheritdoc}
     */
    public function addNamespace(string $namespace, $hints): FinderContract
    {
        $hints = (array) $hints;

        if (isset($this->hints[$namespace])) {
            $hints = array_merge($this->hints[$namespace], $hints);
        }

        $this->hints[$namespace] = $hints;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function prependNamespace(string $namespace, $hints): FinderContract
    {
        $hints = (array) $hints;

        if (isset($this->hints[$namespace])) {
            $hints = array_merge($hints, $this->hints[$namespace]);
        }

        $this->hints[$namespace] = $hints;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function addExtension(string $extension): FinderContract
    {
        if (($index = array_search($extension, $this->extensions, true)) !== false) {
            unset($this->extensions[$index]);
        }

        array_unshift($this->extensions, $extension);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function hasHintInformation(string $name): bool
    {
        return strpos($name, FinderContract::HINT_PATH_DELIMITER) > 0;
    }

    /**
     * {@inheritdoc}
     */
    public function getFilesystem(): FilesystemContract
    {
        return $this->files;
    }

    /**
     * {@inheritdoc}
     */
    public function getPaths(): array
    {
        return $this->paths;
    }

    /**
     * {@inheritdoc}
     */
    public function setPaths(array $paths): FinderContract
    {
        $this->paths = $paths;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getHints(): array
    {
        return $this->hints;
    }

    /**
     * {@inheritdoc}
     */
    public function getExtensions(): array
    {
        return $this->extensions;
    }

    /**
     * {@inheritdoc}
     */
    public function replaceNamespace(string $namespace, $hints): FinderContract
    {
        $this->hints[$namespace] = (array) $hints;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function flush(): void
    {
        $this->views = [];
    }

    /**
     * Get the path to a template with a named path.
     *
     * @param string $name
     *
     * @return array
     */
    protected function findNamedPathView(string $name): array
    {
        list($namespace, $view) = $this->getNamespaceSegments($name);

        return $this->findInPaths($view, $this->hints[$namespace]);
    }

    /**
     * Get the segments of a template with a named path.
     *
     * @param string $name
     *
     * @throws \InvalidArgumentException
     *
     * @return array
     */
    protected function getNamespaceSegments(string $name): array
    {
        $segments = explode(FinderContract::HINT_PATH_DELIMITER, $name);

        if (count($segments) !== 2) {
            throw new InvalidArgumentException(sprintf('View [%s] has an invalid name.', $name));
        }

        if (! isset($this->hints[$segments[0]])) {
            throw new InvalidArgumentException(sprintf('No hint path defined for [%s].', $segments[0]));
        }

        return $segments;
    }

    /**
     * Find the given view in the list of paths.
     *
     * @param string $name
     * @param array  $paths
     *
     * @throws \InvalidArgumentException
     *
     * @return array
     */
    protected function findInPaths(string $name, array $paths): array
    {
        foreach ($paths as $path) {
            foreach ($this->getPossibleViewFiles($name) as $fileInfos) {
                $viewPath = self::normalizeDirectorySeparator($path . '/' . $fileInfos['file']);

                if ($this->files->has($viewPath)) {
                    return [
                        'path' => $viewPath,
                        'name' => $fileInfos['file'],
                        'extension' => $fileInfos['extension'],
                    ];
                }
            }
        }

        throw new InvalidArgumentException(sprintf('View [%s] not found.', $name));
    }

    /**
     * Get an array of possible view files.
     *
     * @param string $name
     *
     * @return array
     */
    protected function getPossibleViewFiles(string $name): array
    {
        return array_map(function ($extension) use ($name) {
            return [
                'extension' => $extension,
                'file' => str_replace('.', DIRECTORY_SEPARATOR, $name) . '.' . $extension,
            ];
        }, $this->extensions);
    }
}
