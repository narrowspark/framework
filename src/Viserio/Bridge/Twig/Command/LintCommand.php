<?php
declare(strict_types=1);
namespace Viserio\Bridge\Twig\Command;

use InvalidArgumentException;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use RuntimeException;
use Twig\Environment;
use Twig\Error\Error;
use Twig\Loader\ArrayLoader;
use Twig\Source;
use UnexpectedValueException;
use Viserio\Component\Console\Command\AbstractCommand;

class LintCommand extends AbstractCommand
{
    /**
     * {@inheritdoc}
     */
    protected static $defaultName = 'lint:twig';

    /**
     * {@inheritdoc}
     */
    protected $signature = 'lint:twig
        [dir=* : Path to the template dir.]
        [--files=* : Lint multiple files. Relative to the view path.]
        [--directories=* : Lint multiple directories. Relative to the view path.]
        [--format=txt : The output format. Supports `txt` or `json`.]
    ';

    /**
     * {@inheritdoc}
     */
    protected $description = 'Lints a templates and outputs encountered errors';

    /**
     * A twig instance.
     *
     * @var \Twig\Environment
     */
    private $environment;

    /**
     * Create a DebugCommand instance.
     *
     * @param \Twig\Environment $environment
     */
    public function __construct(Environment $environment)
    {
        parent::__construct();

        $this->environment = $environment;
    }

    /**
     * {@inheritdoc}
     */
    public function handle(): int
    {
        $files = $this->getFiles((array) $this->option('files'), (array) $this->option('directories'));

        // If no files are found.
        if (\count($files) === 0) {
            throw new RuntimeException('No twig files found.');
        }

        $details = [];

        foreach ($files as $file) {
            $details[] = $this->validate(\file_get_contents($file), $file);
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

        /** @var \SplFileInfo $file */
        foreach ($this->getFinder($directories) as $file) {
            if (\count($files) !== 0 && ! \in_array($file->getFilename(), $files, true)) {
                continue;
            }

            $foundFiles[] = $file->getRealPath();
        }

        return $foundFiles;
    }

    /**
     * Get a finder instance of Twig files in the specified directories.
     *
     * @param array $paths paths to search for files in
     *
     * @return iterable
     */
    protected function getFinder(array $paths): iterable
    {
        $foundFiles = [];
        $baseDir    = (array) $this->argument('dir');

        foreach ($baseDir as $dir) {
            if (\count($paths) !== 0) {
                foreach ($paths as $path) {
                    $this->findTwigFiles($dir . \DIRECTORY_SEPARATOR . $path, $foundFiles);
                }
            } else {
                $this->findTwigFiles($dir, $foundFiles);
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
        $realLoader = $this->environment->getLoader();

        try {
            $temporaryLoader = new ArrayLoader([$file => $template]);

            $this->environment->setLoader($temporaryLoader);
            $nodeTree = $this->environment->parse($this->environment->tokenize(new Source($template, $file)));

            $this->environment->compile($nodeTree);
            $this->environment->setLoader($realLoader);
        } catch (Error $exception) {
            $this->environment->setLoader($realLoader);

            return [
                'template'  => $template,
                'file'      => $file,
                'valid'     => false,
                'exception' => $exception,
            ];
        }

        return [
            'template' => $template,
            'file'     => $file,
            'valid'    => true,
        ];
    }

    /**
     * Output the results of the linting.
     *
     * @param array  $details validation results from all linted files
     * @param string $format  Format to output the results in. Supports txt or json.
     *
     * @throws \InvalidArgumentException thrown for an unknown format
     *
     * @return int
     */
    protected function display(array $details, string $format = 'txt'): int
    {
        $verbose = $this->getOutput()->isVerbose();

        switch ($format) {
            case 'txt':
                return $this->displayText($details, $verbose);
            case 'json':
                return $this->displayJson($details);
            default:
                throw new InvalidArgumentException(\sprintf('The format [%s] is not supported.', $format));
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
            if ($verbose && $info['valid']) {
                $file = ' in ' . $info['file'];
                $this->line('<info>OK</info>' . $file);
            } elseif (! $info['valid']) {
                $errors++;
                $this->renderException($info);
            }
        }

        if ($errors === 0) {
            $this->comment(\sprintf('All %d Twig files contain valid syntax.', \count($details)));
        } else {
            $this->warn(\sprintf('%d Twig files have valid syntax and %d contain errors.', \count($details) - $errors, $errors));
        }

        return \min($errors, 1);
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

        \array_walk(
            $details,
            function (&$info) use (&$errors): void {
                $info['file'] = (string) $info['file'];

                unset($info['template']);

                if (! $info['valid']) {
                    $info['message'] = $info['exception']->getMessage();

                    unset($info['exception']);

                    $errors++;
                }
            }
        );

        $this->line(\json_encode($details, \JSON_PRETTY_PRINT | \JSON_UNESCAPED_SLASHES));

        return \min($errors, 1);
    }

    /**
     * Output the error to the console.
     *
     * @param array $info details for the file that failed to be linted
     *
     * @return void
     */
    protected function renderException(array $info): void
    {
        $exception = $info['exception'];
        $line      = $exception->getTemplateLine();
        $lines     = $this->getContext($info['template'], $line);

        $this->line(\sprintf('<error>Fail</error> in %s (line %s)', $info['file'], $line));

        foreach ($lines as $no => $code) {
            $this->line(
                \sprintf(
                    '%s %-6s %s',
                    $no === $line ? '<error>>></error>' : '  ',
                    $no,
                    $code
                )
            );

            if ($no === $line) {
                $this->line(\sprintf('<error>>> %s</error> ', $exception->getRawMessage()));
            }
        }
    }

    /**
     * Grabs the surrounding lines around the exception.
     *
     * @param string     $template contents of Twig template
     * @param int|string $line     line where the exception occurred
     * @param int        $context  number of lines around the line where the exception occurred
     *
     * @return array
     */
    protected function getContext(string $template, $line, int $context = 3): array
    {
        $lines    = \explode("\n", $template);
        $position = \max(0, $line - $context);
        $max      = \min(\count($lines), $line - 1 + $context);
        $result   = [];

        while ($position < $max) {
            $result[$position + 1] = $lines[$position];
            $position++;
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
            if (\pathinfo($file->getRealPath(), \PATHINFO_EXTENSION) === 'twig') {
                $foundFiles[] = $file;
            }
        }
    }
}
