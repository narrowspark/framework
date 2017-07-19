<?php
declare(strict_types=1);
namespace Viserio\Provider\Twig\Command;

use Symfony\Component\Finder\Finder;
use Viserio\Bridge\Twig\Command\LintCommand as BaseLintCommand;
use Viserio\Component\Contracts\OptionsResolver\ProvidesDefaultOptions as ProvidesDefaultOptionsContract;
use Viserio\Component\Contracts\OptionsResolver\RequiresComponentConfig as RequiresComponentConfigContract;
use Viserio\Component\Contracts\View\Finder as FinderContract;
use Viserio\Component\OptionsResolver\Traits\OptionsResolverTrait;

class LintCommand extends BaseLintCommand implements RequiresComponentConfigContract, ProvidesDefaultOptionsContract
{
    use OptionsResolverTrait;

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
    public static function getDefaultOptions(): iterable
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
    protected function getArguments(): array
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    protected function getFiles(array $files, array $directories): array
    {
        // Get files from passed in options
        $search            = [];
        $finder            = $this->getContainer()->get(FinderContract::class);
        $paths             = $finder->getPaths();
        $hints             = $finder->getHints();
        $searchDirectories = [];

        if (\is_array($hints) && \count($hints) !== 0) {
            $paths = \array_reduce($hints, function ($package, $paths) {
                return \array_merge($paths, $package);
            }, $paths);
        }

        if (\count($directories) !== 0) {
            foreach ($directories as $directory) {
                foreach ($paths as $path) {
                    if (\is_dir($this->normalizeDirectorySeparator($path . '/' . $directory))) {
                        $searchDirectories[] = $this->normalizeDirectorySeparator($path . '/' . $directory);
                    } else {
                        $this->warn('Path "' . $this->normalizeDirectorySeparator($path . '/' . $directory) . '" is not a directory.');
                    }
                }
            }

            if (\count($searchDirectories) !== 0 && \count($files) === 0) {
                // Get those files from the search directory
                foreach ($this->getFinder($searchDirectories) as $file) {
                    $search[] = $this->normalizeDirectorySeparator($file->getRealPath());
                }
            }
        }

        if (\count($files) !== 0) {
            $search = \array_merge($search, $this->findArgumentFiles($paths, $searchDirectories, $files));
        }

        // If no files passed, use the view paths
        if (\count($search) === 0) {
            foreach ($this->getFinder($paths) as $file) {
                $search[] = $this->normalizeDirectorySeparator($file->getRealPath());
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
                foreach ($this->getFinder($searchDirectories, $fileName) as $file) {
                    $search[] = $this->normalizeDirectorySeparator($file->getRealPath());
                }
            } else {
                foreach ($this->getFinder($paths, $fileName) as $file) {
                    $search[] = $this->normalizeDirectorySeparator($file->getRealPath());
                }
            }
        }

        return $search;
    }

    /**
     * {@inheritdoc}
     */
    protected function getFinder(array $paths, string $file = null): iterable
    {
        $options = self::resolveOptions($this->getContainer());

        return Finder::create()
            ->files()
            ->in($paths)
            ->name(($file === null ? '*.' : $file . '.') . $options['engines']['twig']['file_extension'])
            ->getIterator();
    }
}
