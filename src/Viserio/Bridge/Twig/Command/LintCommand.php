<?php
declare(strict_types=1);
namespace Viserio\Bridge\Twig\Command;

use InvalidArgumentException;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use RuntimeException;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Finder\Finder;
use Twig\Environment;
use Twig\Error\Error;
use Twig\Loader\ArrayLoader;
use Twig\Source;
use UnexpectedValueException;
use Viserio\Component\Console\Command\Command;
use Viserio\Component\Support\Traits\NormalizePathAndDirectorySeparatorTrait;

class LintCommand extends Command
{
    use NormalizePathAndDirectorySeparatorTrait;

    /**
     * {@inheritdoc}
     */
    protected $name = 'twig:lint';

    /**
     * {@inheritdoc}
     */
    protected $description = 'Lints a templates and outputs encountered errors';

    /**
     * {@inheritdoc}
     */
    public function handle()
    {
        $container = $this->getContainer();

        if (! $container->has(Environment::class)) {
            $this->error('The Twig environment needs to be set.');

            return;
        }

        $files = $this->getFiles((array) $this->option('files'), (array) $this->option('directories'));

        // If no files are found.
        if (count($files) === 0) {
            throw new RuntimeException('No twig files found.');
        }

        $details = [];

        foreach ($files as $file) {
            $details[] = $this->validate(file_get_contents($file), $file);
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
        $foundFiles = [];

        foreach ($this->getFinder($directories) as $file) {
            if (count($files) !== 0 && ! in_array($file->getFilename(), $files, true)) {
                continue;
            }

            $foundFiles[] = $this->normalizeDirectorySeparator($file->getRealPath());
        }

        return $foundFiles;
    }

    /**
     * {@inheritdoc}
     */
    protected function getArguments(): array
    {
        return [
            [
                'dir',
                InputArgument::IS_ARRAY | InputArgument::REQUIRED,
                'Path to the template dir.',
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
                'Lint multiple files. Relative to the view path.',
            ],
            [
                'directories',
                null,
                InputOption::VALUE_IS_ARRAY | InputOption::VALUE_REQUIRED,
                'Lint multiple directories. Relative to the view path.',
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
     * @return iterable
     */
    protected function getFinder(array $paths, string $file = null): iterable
    {
        $foundFiles   = [];
        $baseDir      = (array) $this->argument('dir');

        foreach ($baseDir as $dir) {
            if (count($paths) !== 0) {
                foreach ($paths as $path) {
                    $this->findTwigFiles($this->normalizeDirectorySeparator($dir . '/' . $path), $foundFiles);
                }
            } else {
                $this->findTwigFiles($this->normalizeDirectorySeparator($dir), $foundFiles);
            }
        }

        return $foundFiles;
    }

    /**
     * Validate the template.
     *
     * @param string $template twig template
     * @param string $file
     *
     * @return array
     */
    protected function validate(string $template, string $file): array
    {
        $twig       = $this->getContainer()->get(Environment::class);
        $realLoader = $twig->getLoader();

        try {
            $temporaryLoader = new ArrayLoader([$file => $template]);

            $twig->setLoader($temporaryLoader);
            $nodeTree = $twig->parse($twig->tokenize(new Source($template, $file)));

            $twig->compile($nodeTree);
            $twig->setLoader($realLoader);
        } catch (Error $exception) {
            $twig->setLoader($realLoader);

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
                $file = ' in ' . $info['file'];
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
        $exception = $info['exception'];
        $line      = $exception->getTemplateLine();
        $lines     = $this->getContext($info['template'], $line);

        $this->line(sprintf('<error>Fail</error> in %s (line %s)', $info['file'], $line));

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
     * @param string     $template contents of Twig template
     * @param string|int $line     line where the exception occurred
     * @param int        $context  number of lines around the line where the exception occurred
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
            ++$position;
        }

        return $result;
    }

    /**
     * Undocumented function.
     *
     * @param string $dir
     * @param array  $foundFiles
     *
     * @throws \RuntimeException
     *
     * @return void
     */
    private function findTwigFiles(string $dir, array &$foundFiles): void
    {
        try {
            $iterator = new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator($dir, RecursiveDirectoryIterator::SKIP_DOTS),
                RecursiveIteratorIterator::SELF_FIRST
            );
        } catch (UnexpectedValueException $exception) {
            throw new RuntimeException($exception->getMessage());
        }

        foreach ($iterator as $file) {
            if (pathinfo($file->getRealPath(), PATHINFO_EXTENSION) === 'twig') {
                $foundFiles[] = $file;
            }
        }
    }
}
