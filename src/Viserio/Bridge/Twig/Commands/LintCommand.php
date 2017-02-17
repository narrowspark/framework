<?php
declare(strict_types=1);
namespace Viserio\Bridge\Twig\Commands;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Finder\Finder;
use Twig_Environment;
use Twig_Error_Loader;
use RuntimeException;
use InvalidArgumentException;
use Twig_LoaderInterface;
use Twig_Error;
use Viserio\Component\Console\Command\Command;
use Viserio\Component\Contracts\OptionsResolver\RequiresComponentConfig as RequiresComponentConfigContract;
use Viserio\Component\OptionsResolver\Traits\ConfigurationTrait;
use Viserio\Component\Contracts\View\Finder as FinderContract;
use Viserio\Component\Support\Traits\NormalizePathAndDirectorySeparatorTrait;
use Viserio\Component\Contracts\OptionsResolver\ProvidesDefaultOptions as ProvidesDefaultOptionsContract;

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

        if ($container->has(Twig_Environment::class)) {
            $this->error('The Twig environment needs to be set.');

            return 1;
        }

        $format = $this->option('format');

        // Check STDIN for the template
        if (ftell(STDIN) === 0) {
            // Read template in
            $template = '';

            while (!feof(STDIN)) {
                $template .= fread(STDIN, 1024);
            }

            return $this->display([$this->validate($template)], $format);
        }

        $files   = $this->getFiles($this->argument('filename'), $this->option('file'), $this->option('directory'));
        $details = [];

        foreach ($files as $file) {
            try {
                $template = $this->getContents($file);
            } catch (Twig_Error_Loader $exception) {
                throw new RuntimeException(sprintf('File or directory "%s" is not readable', $file));
            }

            $details[] = $this->validate($template, $file);
        }

        return $this->display($details, $format);
    }

    /**
     * Gets an array of files to lint.
     *
     * @param string $filename    Single file to check.
     * @param array  $files       Array of files to check.
     * @param array  $directories Array of directories to get the files from.
     *
     * @return array
     */
    protected function getFiles(string $filename, array $files, array $directories): array
    {
        // Get files from passed in options
        $search = $files;
        $finder = $this->getContainer()->get(FinderContract::class)->getFinder();
        $paths  = $finder->getPaths();
        $hints  = $finder->getHints();

        if (is_array($hints) && !empty($hints)) {
            $paths = array_reduce($hints, function ($package, $paths) {
                return array_merge($paths, $package);
            }, $paths);
        }

        if (!empty($filename)) {
            $search[] = $filename;
        }

        if (!empty($directories)) {
            $search_directories = [];

            foreach ($directories as $directory) {
                foreach ($paths as $path) {
                    if (is_dir($this->normalizeDirectorySeparator($path.'/'.$directory))) {
                        $search_directories[] = $this->normalizeDirectorySeparator($path.'/'.$directory);
                    }
                }
            }

            if (!empty($search_directories)) {
                // Get those files from the search directory
                foreach ($this->getFinder($search_directories) as $file) {
                    $search[] = $file->getRealPath();
                }
            }
        }

        // If no files passed, use the view paths
        if (empty($search)) {
            foreach ($this->getFinder($paths) as $file) {
                $search[] = $file->getRealPath();
            }
        }

        return $search;
    }

    /**
     * {@inheritdoc}
     */
    protected function getArguments(): array
    {
        return [
            [
                'filenames',
                InputArgument::OPTIONAL,
                'Filename or directory to lint. If none supplied, all views will be checked.',
            ],
        ];
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
     * @param array $paths Paths to search for files in.
     *
     * @return \Symfony\Component\Finder\Finder
     */
    protected function getFinder(array $paths): Finder
    {
        return Finder::create()
            ->files()
            ->in($paths)
            ->name('*.' . $this->options['engines']['twig']['file_extension']);
    }

    /**
     * Validate the template.
     *
     * @param string      $template Twig template.
     * @param string|null $file     Filename of the template.
     *
     * @return array
     */
    protected function validate(string $template, ?string $file = null): array
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
     * @param array  $details Validation results from all linted files.
     * @param string $format  Format to output the results in. Supports text or json.
     *
     * @throws \InvalidArgumentException Thrown for an unknown format.
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
                return $this->displayJson($details, $verbose);
            default:
                throw new InvalidArgumentException(sprintf('The format "%s" is not supported.', $format));
        }
    }

    /**
     * Output the results as text.
     *
     * @param array $details Validation results from all linted files.
     * @param bool  $verbose
     *
     * @return int
     */
    protected function displayText(array $details, bool $verbose = false): int
    {
        $errors = 0;

        foreach ($details as $info) {
            if ($info['valid'] && $verbose) {
                $file = ($info['file']) ? ' in '.$info['file'] : '';
                $this->line('<info>OK</info>'.$file);
            } elseif (!$info['valid']) {
                $errors++;
                $this->renderException($info);
            }
        }

        if ($errors === 0) {
            $this->comment(sprintf('All %d Twig files contain valid syntax.', count($details)));
        } else {
            $this->warning(sprintf('%d Twig files have valid syntax and %d contain errors.', count($details) - $errors, $errors));
        }

        return min($errors, 1);
    }

    /**
     * Output the results as json.
     *
     * @param array $details Validation results from all linted files.
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

                    $errors++;
                }
            }
        );

        $this->line(json_encode($details, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

        return min($errors, 1);
    }

    /**
     * Output the error to the console.
     *
     * @param array Details for the file that failed to be linted.
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
                    "%s %-6s %s",
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
     * @param string     $template Contents of Twig template.
     * @param string|int $line     Line where the exception occurred.
     * @param int        $context  Number of lines around the line where the exception occurred.
     *
     * @return array
     */
    protected function getContext(string $template, $line, int $context = 3): array
    {
        $lines    = explode("\n", $template);
        $position = max(0, $line - $context);
        $max      = min(count($lines), $line - 1 + $context);
        $result   = [];

        while ($position < $max) {
            $result[$position + 1] = $lines[$position];
            $position++;
        }

        return $result;
    }

    /**
     * Get the contents of the template.
     *
     * @param string $file
     *
     * @return string
     */
    protected function getContents(string $file): string
    {
        return $this->getContainer()->get(Twig_LoaderInterface::class)->getSource($file);
    }
}
