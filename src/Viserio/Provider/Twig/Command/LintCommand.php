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

namespace Viserio\Provider\Twig\Command;

use ArrayAccess;
use SplFileObject;
use Symfony\Component\Finder\Finder;
use Twig\Environment;
use Viserio\Bridge\Twig\Command\LintCommand as BaseLintCommand;
use Viserio\Component\OptionsResolver\Traits\OptionsResolverTrait;
use Viserio\Contract\OptionsResolver\ProvidesDefaultOption as ProvidesDefaultOptionContract;
use Viserio\Contract\OptionsResolver\RequiresComponentConfig as RequiresComponentConfigContract;
use Viserio\Contract\View\Finder as FinderContract;

class LintCommand extends BaseLintCommand implements ProvidesDefaultOptionContract, RequiresComponentConfigContract
{
    use OptionsResolverTrait;

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
     * Resolved options.
     *
     * @var array
     */
    private $resolvedOptions;

    /**
     * A view finder instance.
     *
     * @var \Viserio\Contract\View\Finder
     */
    private $finder;

    /**
     * Create a DebugCommand instance.
     *
     * @param \Twig\Environment             $environment
     * @param \Viserio\Contract\View\Finder $finder
     * @param array|ArrayAccess             $config
     */
    public function __construct(Environment $environment, FinderContract $finder, $config)
    {
        parent::__construct($environment);

        $this->finder = $finder;
        $this->resolvedOptions = self::resolveOptions($config);
    }

    /**
     * {@inheritdoc}
     */
    protected function getFinder(array $paths, ?string $file = null): iterable
    {
        return Finder::create()
            ->files()
            ->in($paths)
            ->name(($file === null ? '*.' : $file . '.') . $this->resolvedOptions['engines']['twig']['file_extension'])
            ->getIterator();
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
    public static function getDefaultOptions(): array
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
                    $path = $path . \DIRECTORY_SEPARATOR . $directory;

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
     *
     * @param array $paths
     * @param array $searchDirectories
     * @param array $files
     *
     * @return array
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
