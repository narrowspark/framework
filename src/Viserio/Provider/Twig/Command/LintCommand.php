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

namespace Viserio\Provider\Twig\Command;

use SplFileObject;
use Twig\Environment;
use Viserio\Bridge\Twig\Command\LintCommand as BaseLintCommand;
use Viserio\Component\Finder\Finder;
use Viserio\Contract\Config\ProvidesDefaultConfig as ProvidesDefaultConfigContract;
use Viserio\Contract\Config\RequiresComponentConfig as RequiresComponentConfigContract;
use Viserio\Contract\View\Finder as FinderContract;

class LintCommand extends BaseLintCommand implements ProvidesDefaultConfigContract, RequiresComponentConfigContract
{
    /**
     * {@inheritdoc}
     */
    protected static $defaultName = 'lint:twig';

    /**
     * {@inheritdoc}
     */
    protected $signature = 'lint:twig
        [--files=* : Lint multiple files. Relative to the view path.]
        [--directories=* : Lint multiple directories. Relative to the view path.]
        [--format=txt : The output format. Supports `txt` or `json`.]
        [--show-deprecations : Show deprecations as errors]
    ';

    /**
     * A view finder instance.
     *
     * @var \Viserio\Contract\View\Finder
     */
    private $finder;

    /**
     * Twig file extension name.
     */
    private string $fileExtension;

    /**
     * Create a DebugCommand instance.
     */
    public function __construct(Environment $environment, FinderContract $finder, string $fileExtension)
    {
        parent::__construct($environment);

        $this->finder = $finder;
        $this->fileExtension = $fileExtension;
    }

    /**
     * {@inheritdoc}
     */
    public static function getDimensions(): iterable
    {
        return ['viserio', 'view'];
    }

    /**
     * {@inheritdoc}
     */
    public static function getDefaultConfig(): iterable
    {
        return [
            'engines' => [
                'twig' => [
                    'file_extension' => 'twig',
                ],
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    protected function getFinder(array $paths, ?string $file = null): iterable
    {
        return Finder::create()
            ->files()
            ->in($paths)
            ->name(($file === null ? '*.' : $file . '.') . $this->fileExtension)
            ->getIterator();
    }

    /**
     * {@inheritdoc}
     */
    protected function getFiles(array $files, array $directories): array
    {
        // Get files from passed in options
        $search = [];
        $paths = $this->finder->getPaths();
        $hints = $this->finder->getHints();
        $searchDirectories = [];

        if (\count($hints) !== 0) {
            $paths = \array_reduce($hints, static function ($package, $paths) {
                return \array_merge($paths, $package);
            }, $paths);
        }

        if (\count($directories) !== 0) {
            foreach ($directories as $directory) {
                foreach ($paths as $path) {
                    $path .= \DIRECTORY_SEPARATOR . $directory;

                    if (\is_dir($path)) {
                        $searchDirectories[] = $path;
                    } else {
                        $this->warn('Path "' . $path . '" is not a directory.');
                    }
                }
            }

            if (\count($searchDirectories) !== 0 && \count($files) === 0) {
                // Get those files from the search directory
                /** @var SplFileObject $file */
                foreach ($this->getFinder($searchDirectories) as $file) {
                    $search[] = $file->getRealPath();
                }
            }
        }

        if (\count($files) !== 0) {
            $search = \array_merge($search, $this->findArgumentFiles($paths, $searchDirectories, $files));
        }

        // If no files passed, use the view paths
        if (\count($search) === 0) {
            /** @var SplFileObject $file */
            foreach ($this->getFinder($paths) as $file) {
                $search[] = $file->getRealPath();
            }
        }

        return $search;
    }

    /**
     * Gets an array of argument files to lint.
     */
    protected function findArgumentFiles(array $paths, array $searchDirectories, array $files): array
    {
        $search = [];

        foreach ($files as $fileName) {
            if (\count($searchDirectories) !== 0) {
                /** @var SplFileObject $file */
                foreach ($this->getFinder($searchDirectories, $fileName) as $file) {
                    $search[] = $file->getRealPath();
                }
            } else {
                /** @var SplFileObject $file */
                foreach ($this->getFinder($paths, $fileName) as $file) {
                    $search[] = $file->getRealPath();
                }
            }
        }

        return $search;
    }
}
