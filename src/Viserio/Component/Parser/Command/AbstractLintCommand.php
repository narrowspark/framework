<?php
declare(strict_types=1);
namespace Viserio\Component\Parser\Command;

use FilesystemIterator;
use Generator;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use Viserio\Component\Console\Command\AbstractCommand;
use Viserio\Component\Contract\Parser\Exception\InvalidArgumentException;
use Viserio\Component\Contract\Parser\Exception\RuntimeException;

abstract class AbstractLintCommand extends AbstractCommand
{
    /**
     * {@inheritdoc}
     *
     * @throws \Viserio\Component\Contract\Parser\Exception\RuntimeException
     */
    public function handle(): int
    {
        $filename            = $this->argument('filename');
        $format              = $this->option('format');
        $displayCorrectFiles = $this->getOutput()->isVerbose();

        if (! $filename) {
            if (! $stdin = $this->getStdin()) {
                throw new RuntimeException('Please provide a filename or pipe file content to STDIN.');
            }

            return $this->display([$this->validate($stdin)], $format, $displayCorrectFiles);
        }

        if (! \is_readable($filename)) {
            throw new RuntimeException(\sprintf('File or directory [%s] is not readable.', $filename));
        }

        $filesInfo = [];

        foreach ($this->getFiles($filename) as $file) {
            $filesInfo[] = $this->validate(\file_get_contents($file), $file);
        }

        return $this->display($filesInfo, $format, $displayCorrectFiles);
    }

    /**
     * Get display type from format.
     *
     * @param string $format
     * @param array  $files
     * @param bool   $displayCorrectFiles
     *
     * @throws \Viserio\Component\Contract\Parser\Exception\InvalidArgumentException
     *
     * @return int
     */
    protected function display(array $files, string $format, bool $displayCorrectFiles): int
    {
        switch ($format) {
            case 'txt':
                return $this->displayTxt($files, $displayCorrectFiles);
            case 'json':
                return $this->displayJson($files);

            default:
                throw new InvalidArgumentException(\sprintf('The format [%s] is not supported.', $format));
        }
    }

    /**
     * Display errors in txt format.
     *
     * @param array $filesInfo
     * @param bool  $displayCorrectFiles
     *
     * @return int
     */
    abstract protected function displayTxt(array $filesInfo, bool $displayCorrectFiles): int;

    /**
     * Display errors in json format.
     *
     * @param array $filesInfo
     *
     * @return int
     */
    protected function displayJson(array $filesInfo): int
    {
        $errors = 0;

        \array_walk($filesInfo, static function (&$v) use (&$errors): void {
            $v['file'] = (string) $v['file'];

            if (! $v['valid']) {
                $errors++;
            }
        });

        $this->getOutput()->writeln(\json_encode($filesInfo, \JSON_PRETTY_PRINT | \JSON_UNESCAPED_SLASHES));

        return \min($errors, 1);
    }

    /**
     * Validate file content.
     *
     * @param string      $content
     * @param null|string $file
     *
     * @return array
     */
    abstract protected function validate(string $content, ?string $file = null): array;

    /**
     * Get a generator of files.
     *
     * @param string $fileOrDirectory
     *
     * @return \Generator
     */
    protected function getFiles(string $fileOrDirectory): Generator
    {
        if (\is_file($fileOrDirectory)) {
            yield $fileOrDirectory;

            return;
        }

        foreach (self::getDirectoryIterator($fileOrDirectory) as $file) {
            if (! \in_array($file->getExtension(), ['xlf', 'xliff'], true)) {
                continue;
            }

            yield (string) $file;
        }
    }

    /**
     * Get content from stdin.
     *
     * @return null|string
     */
    protected function getStdin(): ?string
    {
        if (\ftell(\STDIN) !== 0) {
            return null;
        }

        $inputs = '';

        while (! \feof(\STDIN)) {
            $inputs .= \fread(\STDIN, 1024);
        }

        return $inputs;
    }

    /**
     * Get item from dirs.
     *
     * @param string $directory
     *
     * @return \RecursiveIteratorIterator
     */
    protected static function getDirectoryIterator(string $directory): RecursiveIteratorIterator
    {
        return new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($directory, FilesystemIterator::SKIP_DOTS | FilesystemIterator::FOLLOW_SYMLINKS),
            RecursiveIteratorIterator::LEAVES_ONLY
        );
    }
}
