<?php
declare(strict_types=1);
namespace Viserio\Bridge\Twig\Commands;

use InvalidArgumentException;
use RuntimeException;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Finder\Finder;
use Twig_Environment;
use Twig_Error;
use Twig_Error_Loader;
use Twig_LoaderInterface;
use Twig_Source;
use Viserio\Component\Console\Command\Command;
use Viserio\Component\Contracts\OptionsResolver\ProvidesDefaultOptions as ProvidesDefaultOptionsContract;
use Viserio\Component\Contracts\OptionsResolver\RequiresComponentConfig as RequiresComponentConfigContract;
use Viserio\Component\Contracts\View\Finder as FinderContract;
use Viserio\Component\OptionsResolver\Traits\ConfigurationTrait;
use Viserio\Component\Support\Traits\NormalizePathAndDirectorySeparatorTrait;

class LintCommand extends Command implements RequiresComponentConfigContract, ProvidesDefaultOptionsContract
{
    use ConfigurationTrait;
    use NormalizePathAndDirectorySeparatorTrait;

    /**
     * {@inheritdoc}
     */
    protected $name = 'twig:lint';

    /**
     * {@inheritdoc}
     */
    protected $description = 'Lints a template and outputs encountered errors';

    /**
     * {@inheritdoc}
     */
    public function getDimensions(): iterable
    {
        return ['viserio', 'view'];
    }

    /**
     * {@inheritdoc}
     */
    public function getDefaultOptions(): iterable
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
    public function handle()
    {
        $container = $this->getContainer();

        $this->configureOptions($container);

        if (! $container->has(Twig_Environment::class)) {
            $this->error('The Twig environment needs to be set.');

            return;
        }

        $files   = $this->getFiles((array) $this->option('files'), (array) $this->option('directories'));
        $details = [];

        // If no files are found.
        if (count($files) === 0) {
            throw new RuntimeException('No twig files found.');
        }

        foreach ($files as $file) {
            try {
                $template = $container->get(Twig_LoaderInterface::class)->getSourceContext($file);
            } catch (Twig_Error_Loader $exception) {
                throw new RuntimeException(sprintf('File or directory [%s] is not reaDBALe', $file));
            }

            $details[] = $this->validate($template, $file);
        }

        return $this->display($details, $this->option('format'));
    }

    /**
     * Gets an array of files to lint.
     *
     * @param array $files       array of files to check
     * @param array $directories array of directories to get the files from
     *
     * @return array
     */
    protected function getFiles(array $files, array $directories): array
    {
        // Get files from passed in options
        $search            = [];
        $finder            = $this->getContainer()->get(FinderContract::class);
        $paths             = $finder->getPaths();
        $hints             = $finder->getHints();
        $searchDirectories = [];

        if (is_array($hints) && count($hints) !== 0) {
            $paths = array_reduce($hints, function ($package, $paths) {
                return array_merge($paths, $package);
            }, $paths);
        }

        if (count($directories) !== 0) {
            foreach ($directories as $directory) {
                foreach ($paths as $path) {
                    if (is_dir($this->normalizeDirectorySeparator($path . '/' . $directory))) {
                        $searchDirectories[] = $this->normalizeDirectorySeparator($path . '/' . $directory);
                    } else {
                        $this->warn('Path "' . $this->normalizeDirectorySeparator($path . '/' . $directory) . '" is not a directory.');
                    }
                }
            }

            if (count($searchDirectories) !== 0 && count($files) === 0) {
                // Get those files from the search directory
                foreach ($this->getFinder($searchDirectories) as $file) {
                    $search[] = $this->normalizeDirectorySeparator($file->getRealPath());
                }
            }
        }

        if (count($files) !== 0) {
            $search = array_merge($search, $this->findArgumentFiles($paths, $searchDirectories, $files));
        }

        // If no files passed, use the view paths
        if (count($search) === 0) {
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
            if (count($searchDirectories) !== 0) {
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
    protected function getOptions(): array
    {
        return [
            [
                'files',
                null,
                InputOption::VALUE_IS_ARRAY | InputOption::VALUE_REQUIRED,
                'Lint multiple files. Relative to the view path. Supports the dot syntax.',
            ],
            [
                'directories',
                null,
                InputOption::VALUE_IS_ARRAY | InputOption::VALUE_REQUIRED,
                'Lint multiple directories. Relative to the view path. Does not support the dot syntax.',
            ],
            [
                'format',
                null,
                InputOption::VALUE_REQUIRED,
                'Format to ouput the result in. Supports `text` or `json`.',
                'text',
            ],
        ];
    }

    /**
     * Get a finder instance of Twig files in the specified directories.
     *
     * @param array       $paths paths to search for files in
     * @param string|null $file
     *
     * @return \Symfony\Component\Finder\Finder
     */
    protected function getFinder(array $paths, string $file = null): Finder
    {
        return Finder::create()
            ->files()
            ->in($paths)
            ->name(($file === null ? '*.' : $file . '.') . $this->options['engines']['twig']['file_extension']);
    }

    /**
     * Validate the template.
     *
     * @param \Twig_Source $template twig template
     * @param string|null  $file     filename of the template
     *
     * @return array
     */
    protected function validate(Twig_Source $template, ?string $file = null): array
    {
        $twig = $this->getContainer()->get(Twig_Environment::class);

        try {
            $twig->parse($twig->tokenize($template, $file));
        } catch (Twig_Error $exception) {
            return [
                'template'  => $template,
                'file'      => $file,
                'valid'     => false,
                'exception' => $exception,
            ];
        }

        return [
            'template'  => $template,
            'file'      => $file,
            'valid'     => true,
        ];
    }

    /**
     * Output the results of the linting.
     *
     * @param array  $details validation results from all linted files
     * @param string $format  Format to output the results in. Supports text or json.
     *
     * @throws \InvalidArgumentException thrown for an unknown format
     *
     * @return int
     */
    protected function display(array $details, string $format = 'text'): int
    {
        $verbose = $this->getOutput()->isVerbose();

        switch ($format) {
            case 'text':
                return $this->displayText($details, $verbose);
            case 'json':
                return $this->displayJson($details);
            default:
                throw new InvalidArgumentException(sprintf('The format [%s] is not supported.', $format));
        }
    }

    /**
     * Output the results as text.
     *
     * @param array $details validation results from all linted files
     * @param bool  $verbose
     *
     * @return int
     */
    protected function displayText(array $details, bool $verbose = false): int
    {
        $errors = 0;

        foreach ($details as $info) {
            if ($info['valid'] && $verbose) {
                $file = ($info['file']) ? ' in ' . $info['file'] : '';
                $this->line('<info>OK</info>' . $file);
            } elseif (! $info['valid']) {
                ++$errors;
                $this->renderException($info);
            }
        }

        if ($errors === 0) {
            $this->comment(sprintf('All %d Twig files contain valid syntax.', count($details)));
        } else {
            $this->warn(sprintf('%d Twig files have valid syntax and %d contain errors.', count($details) - $errors, $errors));
        }

        return min($errors, 1);
    }

    /**
     * Output the results as json.
     *
     * @param array $details validation results from all linted files
     *
     * @return int
     */
    protected function displayJson(array $details): int
    {
        $errors = 0;

        array_walk(
            $details,
            function (&$info) use (&$errors) {
                $info['file'] = (string) $info['file'];

                unset($info['template']);

                if (! $info['valid']) {
                    $info['message'] = $info['exception']->getMessage();

                    unset($info['exception']);

                    ++$errors;
                }
            }
        );

        $this->line(json_encode($details, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

        return min($errors, 1);
    }

    /**
     * Output the error to the console.
     *
     * @param array details for the file that failed to be linted
     * @param array $info
     *
     * @return void
     */
    protected function renderException(array $info): void
    {
        $file      = $info['file'];
        $exception = $info['exception'];
        $line      = $exception->getTemplateLine();
        $lines     = $this->getContext($info['template'], $line);

        if ($file) {
            $this->line(sprintf('<error>Fail</error> in %s (line %s)', $file, $line));
        } else {
            $this->line(sprintf('<error>Fail</error> (line %s)', $line));
        }

        foreach ($lines as $no => $code) {
            $this->line(
                sprintf(
                    '%s %-6s %s',
                    $no == $line ? '<error>>></error>' : '  ',
                    $no,
                    $code
                )
            );

            if ($no == $line) {
                $this->line(sprintf('<error>>> %s</error> ', $exception->getRawMessage()));
            }
        }
    }

    /**
     * Grabs the surrounding lines around the exception.
     *
     * @param Twig_Source $template contents of Twig template
     * @param string|int  $line     line where the exception occurred
     * @param int         $context  number of lines around the line where the exception occurred
     *
     * @return array
     */
    protected function getContext(Twig_Source $template, $line, int $context = 3): array
    {
        $template = $template->getCode();
        $lines    = explode("\n", $template);
        $position = max(0, $line - $context);
        $max      = min(count($lines), $line - 1 + $context);
        $result   = [];

        while ($position < $max) {
            $result[$position + 1] = $lines[$position];
            ++$position;
        }

        return $result;
    }
}
