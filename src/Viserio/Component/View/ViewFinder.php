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

namespace Viserio\Component\View;

use Viserio\Component\OptionsResolver\Traits\OptionsResolverTrait;
use Viserio\Contract\OptionsResolver\RequiresComponentConfig as RequiresComponentConfigContract;
use Viserio\Contract\OptionsResolver\RequiresMandatoryOptions as RequiresMandatoryOptionsContract;
use Viserio\Contract\View\Exception\InvalidArgumentException;
use Viserio\Contract\View\Exception\IOException;
use Viserio\Contract\View\Finder as FinderContract;

class ViewFinder implements FinderContract, RequiresComponentConfigContract, RequiresMandatoryOptionsContract
{
    use OptionsResolverTrait;

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
    protected static $extensions = [
        'php',
        'phtml',
        'css',
        'js',
        'md',
    ];

    /**
     * Create a new file view loader instance.
     *
     * @param array|\ArrayAccess $config
     */
    public function __construct($config)
    {
        $options = self::resolveOptions($config);
        $this->paths = $options['paths'];

        if (isset($options['extensions']) && \is_array($options['extensions'])) {
            foreach ($options['extensions'] as $extension) {
                $this->addExtension($extension);
            }
        }
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
        return self::$extensions;
    }

    /**
     * {@inheritdoc}
     */
    public static function getDimensions(): array
    {
        return ['viserio', 'view'];
    }

    /**
     * {@inheritdoc}
     */
    public static function getMandatoryOptions(): array
    {
        return [
            'paths',
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function find(string $name): array
    {
        if (isset($this->views[$name])) {
            return $this->views[$name];
        }

        if ($this->hasHintInformation($name = \trim($name))) {
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
    public function prependLocation(string $location): void
    {
        \array_unshift($this->paths, $location);
    }

    /**
     * {@inheritdoc}
     */
    public function addNamespace(string $namespace, $hints): FinderContract
    {
        $hints = (array) $hints;

        if (isset($this->hints[$namespace])) {
            $hints = \array_merge($this->hints[$namespace], $hints);
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
            $hints = \array_merge($hints, $this->hints[$namespace]);
        }

        $this->hints[$namespace] = $hints;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function addExtension(string $extension): FinderContract
    {
        if (($index = \array_search($extension, self::$extensions, true)) !== false) {
            unset(self::$extensions[$index]);
        }

        \array_unshift(self::$extensions, $extension);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function hasHintInformation(string $name): bool
    {
        return \strpos($name, FinderContract::HINT_PATH_DELIMITER) > 0;
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
     *
     * @codeCoverageIgnore
     */
    public function reset(): void
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
        [$namespace, $view] = $this->getNamespaceSegments($name);

        return $this->findInPaths($view, $this->hints[$namespace]);
    }

    /**
     * Get the segments of a template with a named path.
     *
     * @param string $name
     *
     * @throws \Viserio\Contract\View\Exception\InvalidArgumentException
     *
     * @return array
     */
    protected function getNamespaceSegments(string $name): array
    {
        $segments = \explode(FinderContract::HINT_PATH_DELIMITER, $name);

        if (\count($segments) !== 2) {
            throw new InvalidArgumentException(\sprintf('View [%s] has an invalid name.', $name));
        }

        if (! isset($this->hints[$segments[0]])) {
            throw new InvalidArgumentException(\sprintf('No hint path defined for [%s].', $segments[0]));
        }

        return $segments;
    }

    /**
     * Find the given view in the list of paths.
     *
     * @param string $name
     * @param array  $paths
     *
     * @throws \Viserio\Contract\View\Exception\InvalidArgumentException
     *
     * @return array
     */
    protected function findInPaths(string $name, array $paths): array
    {
        $maxPathLength = \PHP_MAXPATHLEN - 2;

        foreach ($paths as $path) {
            foreach ($this->getPossibleViewFiles($name) as $fileInfos) {
                $viewPath = $path . \DIRECTORY_SEPARATOR . $fileInfos['file'];

                if (\strlen($viewPath) > $maxPathLength) {
                    throw new IOException(\sprintf('Could not check if file exist because path length exceeds %d characters.', $maxPathLength), 0, null, $viewPath);
                }

                if (file_exists($viewPath)) {
                    return [
                        'path' => $viewPath,
                        'name' => $fileInfos['file'],
                        'extension' => $fileInfos['extension'],
                    ];
                }
            }
        }

        throw new InvalidArgumentException(\sprintf('View [%s] not found.', $name));
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
        return \array_map(static function ($extension) use ($name) {
            return [
                'extension' => $extension,
                'file' => \str_replace('.', \DIRECTORY_SEPARATOR, $name) . '.' . $extension,
            ];
        }, self::$extensions);
    }
}
